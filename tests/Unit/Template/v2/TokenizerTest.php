<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Template\v2;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\v2\Token\StringToken;
use verfriemelt\wrapped\_\Template\v2\Token\VariableToken;
use verfriemelt\wrapped\_\Template\v2\Tokenizer;
use Override;

class TokenizerTest extends TestCase
{
    private Tokenizer $tokenizer;

    #[Override]
    public function setUp(): void
    {
        $this->tokenizer = new Tokenizer();
    }

    public function test_return_empty_root(): void
    {
        static::assertSame([], $this->tokenizer->parse('')->children());
    }

    public function test_return_single_token_for_character(): void
    {
        $result = $this->tokenizer->parse('a')->children();

        static::assertCount(1, $result);
        static::assertInstanceOf(StringToken::class, $result[0]);
    }

    public function test_return_variable_expression(): void
    {
        $result = $this->tokenizer->parse('{{ foo }}')->children();

        static::assertCount(1, $result);
        static::assertInstanceOf(VariableToken::class, $result[0]);
        static::assertSame('foo', $result[0]->expression()->expr);
    }

    public function test_return_variable_expression_with_string(): void
    {
        $result = $this->tokenizer->parse('{{ foo }} hello')->children();

        static::assertCount(2, $result);
        static::assertInstanceOf(VariableToken::class, $result[0]);
        static::assertInstanceOf(StringToken::class, $result[1]);

        static::assertSame(' hello', $result[1]->content());
    }
}
