<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class ExpressionTest extends TestCase
{
    public function testNesting(): void
    {
        $exp = new Expression();
        $exp->add(
            (new Expression() )
                ->add(new Value(true))
        );

        static::assertSame('true', $exp->stringify());
    }

    public function testEmpty(): void
    {
        $exp = new Expression();

        $this->expectExceptionObject(new Exception('empty'));
        $exp->stringify();
    }
}
