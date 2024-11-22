<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Database\SQL\Command;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Command\Insert;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;

class InsertTest extends TestCase
{
    public function test_one(): void
    {
        $insert = new Insert(new Identifier('table'));
        $insert->add(new Identifier('column_b'));

        static::assertSame('INSERT INTO table ( column_b )', $insert->stringify());
    }

    public function test_more(): void
    {
        $insert = new Insert(new Identifier('table'));
        $insert->add(new Identifier('column_a'));
        $insert->add(new Identifier('column_b'));

        static::assertSame('INSERT INTO table ( column_a, column_b )', $insert->stringify());
    }
}
