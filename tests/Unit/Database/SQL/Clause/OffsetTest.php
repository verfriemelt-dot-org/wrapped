<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Database\SQL\Clause;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Clause\Offset;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class OffsetTest extends TestCase
{
    public function test_simple(): void
    {
        $offset = new Offset(new Value(1));
        static::assertSame('OFFSET 1', $offset->stringify());
    }
}
