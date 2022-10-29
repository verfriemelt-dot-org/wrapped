<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class SelectTest extends TestCase
{
    public function testTrue(): void
    {
        $select = new Select();
        $select->add(new Value(true));

        static::assertSame('SELECT true', $select->stringify());
    }

    public function testOnePlusOne(): void
    {
        $select = new Select();
        $select->add(
            (new Expression() )
                ->add(new Value(1))
                ->add(new Operator('+'))
                ->add(new Value(3))
        );

        static::assertSame('SELECT 1 + 3', $select->stringify());
    }
}
