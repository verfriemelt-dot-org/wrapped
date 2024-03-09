<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Template\v2;

use Override;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Token\ConditionalToken;
use verfriemelt\wrapped\_\Template\Token\Exception\EmptyContionalExpressionException;
use verfriemelt\wrapped\_\Template\Token\Exception\EmptyRepeaterExpressionException;
use verfriemelt\wrapped\_\Template\Token\Exception\MissingContionalClosingException;
use verfriemelt\wrapped\_\Template\Token\Exception\MissingRepeaterClosingException;
use verfriemelt\wrapped\_\Template\Token\RepeaterToken;
use verfriemelt\wrapped\_\Template\Token\StringToken;
use verfriemelt\wrapped\_\Template\Token\VariableToken;
use verfriemelt\wrapped\_\Template\Tokenizer;

class TokenizerTest extends TestCase
{
    private Tokenizer $tokenizer;

    #[Override]
    public function setUp(): void
    {
        $this->tokenizer = new Tokenizer();
    }

    public function test_empty_root(): void
    {
        static::assertSame([], $this->tokenizer->parse('')->children());
    }

    public function test_single_token_for_character(): void
    {
        $result = $this->tokenizer->parse('a')->children();

        static::assertCount(1, $result);
        static::assertInstanceOf(StringToken::class, $result[0]);
    }

    public function test_variable_expression(): void
    {
        $result = $this->tokenizer->parse('{{ foo }}')->children();

        static::assertCount(1, $result);
        static::assertInstanceOf(VariableToken::class, $result[0]);
        static::assertFalse($result[0]->raw());
        static::assertSame('foo', $result[0]->expression()->expr);
    }

    public function test_variable_unescaped_expression(): void
    {
        $result = $this->tokenizer->parse('{{ !foo }}')->children();

        static::assertCount(1, $result);
        static::assertInstanceOf(VariableToken::class, $result[0]);
        static::assertTrue($result[0]->raw());
        static::assertSame('foo', $result[0]->expression()->expr);
    }

    public function test_variable_formatter_expression(): void
    {
        $result = $this->tokenizer->parse('{{ foo|formatter }}')->children();

        static::assertCount(1, $result);
        static::assertInstanceOf(VariableToken::class, $result[0]);
        static::assertSame('foo', $result[0]->expression()->expr);
        static::assertSame('formatter', $result[0]->formatter());
    }

    public function test_variable_expression_with_string(): void
    {
        $result = $this->tokenizer->parse('{{ foo }} hello')->children();

        static::assertCount(2, $result);
        static::assertInstanceOf(VariableToken::class, $result[0]);
        static::assertInstanceOf(StringToken::class, $result[1]);

        static::assertSame(' hello', $result[1]->content());
        static::assertSame(9, $result[1]->offset);
    }

    public function test_repeater(): void
    {
        $result = $this->tokenizer->parse("{{ repeater='hi' }}hello{{ /repeater='hi' }}")->children();

        static::assertCount(1, $result);
        $repeater = $result[0];
        static::assertInstanceOf(RepeaterToken::class, $repeater);

        static::assertCount(1, $repeater->children());
        static::assertInstanceOf(StringToken::class, $repeater->children()[0] ?? null);
    }

    public function test_empty_repeater(): void
    {
        $result = $this->tokenizer->parse("{{ repeater='hi' }}{{ /repeater='hi' }}")->children();

        static::assertCount(1, $result);
        $repeater = $result[0];
        static::assertInstanceOf(RepeaterToken::class, $repeater);

        static::assertCount(0, $repeater->children());
    }

    public function test_nested_repeater(): void
    {
        $result = $this->tokenizer->parse("{{ repeater='hi' }}{{ repeater='foo' }}{{ /repeater='foo' }}{{ /repeater='hi' }}")->children();

        static::assertCount(1, $result);
        $repeater = $result[0];
        static::assertInstanceOf(RepeaterToken::class, $repeater);

        static::assertCount(1, $repeater->children());
    }

    public function test_nested_repeater_with_variable(): void
    {
        $result = $this->tokenizer->parse("{{ repeater='hi' }}{{ bar }}{{ /repeater='hi' }}")->children();

        static::assertCount(1, $result);
        $repeater = $result[0];
        static::assertInstanceOf(RepeaterToken::class, $repeater);

        static::assertCount(1, $repeater->children());
    }

    public function test_conditional(): void
    {
        $result = $this->tokenizer->parse("{{ if='hi' }}foo{{ /if='hi' }}")->children();

        static::assertCount(1, $result);
        $conditional = $result[0];

        static::assertInstanceOf(ConditionalToken::class, $conditional);
        static::assertFalse($conditional->expression()->negated);
        static::assertCount(1, $conditional->consequent()->children());
    }

    public function test_negated_conditional(): void
    {
        $result = $this->tokenizer->parse("{{ !if='hi' }}foo{{ /if='hi' }}")->children();

        static::assertCount(1, $result);
        $conditional = $result[0];

        static::assertInstanceOf(ConditionalToken::class, $conditional);
        static::assertTrue($conditional->expression()->negated);
        static::assertCount(1, $conditional->consequent()->children());
    }

    public function test_conditional_with_alternative(): void
    {
        $result = $this->tokenizer->parse("{{ !if='hi' }}foo{{else='hi'}}bar{{ /if='hi' }}")->children();

        static::assertCount(1, $result);
        $conditional = $result[0];

        static::assertInstanceOf(ConditionalToken::class, $conditional);
        static::assertTrue($conditional->expression()->negated);
        static::assertCount(1, $conditional->consequent()->children());
        static::assertCount(1, $conditional->alternative()->children());
    }

    public function test_missing_conditional_expression(): void
    {
        static::expectException(EmptyContionalExpressionException::class);
        $this->tokenizer->parse("{{ if='' }}foo{{ /if='' }}")->children();
    }

    public function test_missing_conditional_closing(): void
    {
        static::expectException(MissingContionalClosingException::class);
        $this->tokenizer->parse("{{ if='asd' }}foo")->children();
    }

    public function test_missing_repeater_expression(): void
    {
        static::expectException(EmptyRepeaterExpressionException::class);
        $this->tokenizer->parse("{{ repeater='' }}foo{{ /repeater='' }}")->children();
    }

    public function test_missing_repeater_closing(): void
    {
        static::expectException(MissingRepeaterClosingException::class);
        $this->tokenizer->parse("{{ repeater='asd' }}foo")->children();
    }
}
