<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Clause\From;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class FromTest extends TestCase
{
    public function testSimple(): void
    {
        $from = new From(new Identifier('table'));
        static::assertSame('FROM table', $from->stringify());
    }

    public function testSimpleAlias(): void
    {
        $from = new From(
            (new Identifier('table') )
                ->addAlias(new Identifier('tb'))
        );
        static::assertSame('FROM table AS tb', $from->stringify());
    }

    public function testFromExpression(): void
    {
        $from = new From(
            (new Select() )->add(new Value(true))
        );
        static::assertSame('FROM ( SELECT true )', $from->stringify());
    }
}
