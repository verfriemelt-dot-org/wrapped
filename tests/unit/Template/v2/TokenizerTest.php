<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Template\v2;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\v2\Token\StringToken;
use verfriemelt\wrapped\_\Template\v2\Token\VariableToken;
use verfriemelt\wrapped\_\Template\v2\Tokenizer;
use verfriemelt\wrapped\_\Template\v2\TokenizerException;

class TokenizerTest extends TestCase
{
    public function testCreateParser(): void
    {
        static::assertEmpty((new Tokenizer(''))->getToken()->getChildren());
    }

    public function testParseString(): void
    {
        $token = (new Tokenizer('foo'))->getToken()->getChildren();

        static::assertNotEmpty($token);
        static::assertCount(1, $token);
        static::assertInstanceOf(StringToken::class, $token[0]);
        static::assertSame('foo', $token[0]->content);
    }

    public function testMissingClosingBraces(): void
    {
        static::expectException(TokenizerException::class);

        new Tokenizer('{{');
    }

    public function testParseVariable(): void
    {
        $token = (new Tokenizer('{{ var }}'))->getToken()->getChildren();

        static::assertNotEmpty($token);

        static::assertCount(1, $token);
        static::assertInstanceOf(VariableToken::class, $token[0]);
        static::assertSame('var', $token[0]->query);
        static::assertSame(false, $token[0]->raw);
    }

    public function testParseRawVariable(): void
    {
        $token = (new Tokenizer('{{ !var }}'))->getToken()->getChildren();

        static::assertNotEmpty($token);
        static::assertCount(1, $token);
        static::assertInstanceOf(VariableToken::class, $token[0]);
        static::assertSame('var', $token[0]->query);
        static::assertSame(true, $token[0]->raw);
    }

    public function testParseDotVariable(): void
    {
        $token = (new Tokenizer('{{ foo.bar }}'))->getToken()->getChildren();

        static::assertNotEmpty($token);
        static::assertCount(1, $token);
        static::assertInstanceOf(VariableToken::class, $token[0]);
        static::assertSame('foo.bar', $token[0]->query);
    }

    public function testConsecutiveVars(): void
    {
        $token = (new Tokenizer('{{ var1 }}{{ var2 }}'))->getToken()->getChildren();

        static::assertNotEmpty($token);
        static::assertCount(2, $token);
        static::assertInstanceOf(VariableToken::class, $token[0]);
        static::assertInstanceOf(VariableToken::class, $token[1]);
        static::assertSame('var1', $token[0]->query);
        static::assertSame('var2', $token[1]->query);
    }

    public function testConsumeRemainingString(): void
    {
        $token = (new Tokenizer('{{ var1 }}foo'))->getToken()->getChildren();

        static::assertNotEmpty($token);
        static::assertCount(2, $token);
        static::assertInstanceOf(VariableToken::class, $token[0]);
        static::assertInstanceOf(StringToken::class, $token[1]);
        static::assertSame('var1', $token[0]->query);
        static::assertSame('foo', $token[1]->content);
    }

    public function testConsumePreceedingString(): void
    {
        $token = (new Tokenizer('foo{{ var1 }}'))->getToken()->getChildren();

        static::assertNotEmpty($token);
        static::assertCount(2, $token);
        static::assertInstanceOf(StringToken::class, $token[0]);
        static::assertInstanceOf(VariableToken::class, $token[1]);
        static::assertSame('foo', $token[0]->content);
        static::assertSame('var1', $token[1]->query);
    }
}
