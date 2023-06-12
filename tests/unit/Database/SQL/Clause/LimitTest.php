<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Clause\Limit;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class LimitTest extends TestCase
{
    public function test_simple(): void
    {
        $limit = new Limit(
            new Value(1)
        );
        static::assertSame('LIMIT 1', $limit->stringify());
    }
}
