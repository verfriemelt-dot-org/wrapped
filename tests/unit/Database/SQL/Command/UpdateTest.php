<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Command\Update;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class UpdateTest extends TestCase
{
    public function testEmptyStatement(): void
    {
        $update = new Update(new Identifier('table'));

        $this->expectExceptionObject(new Exception('empty'));
        $update->stringify();
    }

    public function testSimple(): void
    {
        $update = new Update(new Identifier('table'));
        $update->add(new Identifier('test'), new Value(1));

        $expected = 'UPDATE table SET test = 1';

        static::assertSame($expected, $update->stringify());
    }

    public function testComplex(): void
    {
        $update = new Update(new Identifier('table'));
        $update->add(new Identifier('test'), new Value(1));
        $update->add(
            new Identifier('complex'),
            (new Expression() )
                ->add(new Identifier('complex'))
                ->add(new Operator('+'))
                ->add(new Value(1))
        );

        $expected = 'UPDATE table SET test = 1, complex = complex + 1';

        static::assertSame($expected, $update->stringify());
    }
}
