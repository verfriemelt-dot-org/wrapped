<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Database\SQL\Command;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class SelectTest extends TestCase
{
    public function test_true(): void
    {
        $select = new Select();
        $select->add(new Value(true));

        static::assertSame('SELECT true', $select->stringify());
    }

    public function test_one_plus_one(): void
    {
        $select = new Select();
        $select->add(
            (new Expression())
                ->add(new Value(1))
                ->add(new Operator('+'))
                ->add(new Value(3))
        );

        static::assertSame('SELECT 1 + 3', $select->stringify());
    }
}
