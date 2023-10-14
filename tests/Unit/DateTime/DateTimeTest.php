<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DateTime\DateTime;
use verfriemelt\wrapped\_\DateTime\DateTimeImmutable;

class DateTimeTest extends TestCase
{
    public function test_returns_new_date_time_after_modification(): void
    {
        $a = new DateTime();
        $b = $a->modify('+1 day');

        static::assertTrue($b === $a);
    }

    public function test_date_time_immuteable(): void
    {
        $a = new DateTimeImmutable();
        $b = $a->modify('+1 day');

        static::assertTrue($b !== $a);
    }
}
