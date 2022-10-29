<?php

declare(strict_types=1);

namespace tests\unit\DateTime;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DateTime\DateTime;
use verfriemelt\wrapped\_\DateTime\DateTimeImmutable;

class DateTimeTest extends TestCase
{
    public function testReturnsNewDateTimeAfterModification(): void
    {
        $a = new DateTime();
        $b = $a->modify('+1 day');

        static::assertTrue($b === $a);
    }

    public function testDateTimeImmuteable(): void
    {
        $a = new DateTimeImmutable();
        $b = $a->modify('+1 day');

        static::assertTrue($b !== $a);
    }
}
