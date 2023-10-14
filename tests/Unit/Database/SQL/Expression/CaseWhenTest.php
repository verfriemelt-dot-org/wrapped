<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Database\SQL\Expression;

use Exception;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Expression\CaseWhen;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class CaseWhenTest extends TestCase
{
    public function test_empty(): void
    {
        $this->expectExceptionObject(new Exception('empty'));
        $when = new CaseWhen();
        $when->stringify();
    }

    public function test_minimal(): void
    {
        $when = new CaseWhen();
        $when->when(new Value(true), new Value(false));
        static::assertSame('CASE WHEN true THEN false END', $when->stringify());
    }

    public function test_minimal_else(): void
    {
        $when = new CaseWhen();
        $when->when(new Value(true), new Value(false));
        $when->else(new Value(null));
        static::assertSame('CASE WHEN true THEN false ELSE NULL END', $when->stringify());
    }

    public function test_multiple_when(): void
    {
        $when = new CaseWhen();
        $when->when(new Value(true), new Value(false));
        $when->when(new Value(false), new Value(true));
        $when->else(new Value(null));
        static::assertSame('CASE WHEN true THEN false WHEN false THEN true ELSE NULL END', $when->stringify());
    }

    public function test_switch_style(): void
    {
        $when = new CaseWhen(new Value(1));
        $when->when(new Value(1), new Value(false));
        $when->when(new Value(2), new Value(true));
        static::assertSame('CASE 1 WHEN 1 THEN false WHEN 2 THEN true END', $when->stringify());
    }

    public function test_switch_style_else(): void
    {
        $when = new CaseWhen(new Value(1));
        $when->when(new Value(1), new Value(false));
        $when->when(new Value(2), new Value(true));
        $when->else(new Value(null));
        static::assertSame('CASE 1 WHEN 1 THEN false WHEN 2 THEN true ELSE NULL END', $when->stringify());
    }
}
