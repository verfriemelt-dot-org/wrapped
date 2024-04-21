<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Database\SQL\Expression;

use Exception;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class ExpressionTest extends TestCase
{
    public function test_nesting(): void
    {
        $exp = new Expression();
        $exp->add(
            (new Expression())
                ->add(new Value(true)),
        );

        static::assertSame('true', $exp->stringify());
    }

    public function test_empty(): void
    {
        $exp = new Expression();

        $this->expectExceptionObject(new Exception('empty'));
        $exp->stringify();
    }
}
