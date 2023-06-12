<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Clause\Where;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class WhereTest extends TestCase
{
    public function test_simple(): void
    {
        $where = new Where(new Value(true));
        static::assertSame('WHERE true', $where->stringify());
    }
}
