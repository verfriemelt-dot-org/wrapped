<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Expression\CaseWhen;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class CaseWhenTest extends TestCase
{
    public function testEmpty(): void
    {
        $this->expectExceptionObject(new Exception('empty'));
        $when = new CaseWhen();
        $when->stringify();
    }

    public function testMinimal(): void
    {
        $when = new CaseWhen();
        $when->when(new Value(true), new Value(false));
        static::assertSame('CASE WHEN true THEN false END', $when->stringify());
    }

    public function testMinimalElse(): void
    {
        $when = new CaseWhen();
        $when->when(new Value(true), new Value(false));
        $when->else(new Value(null));
        static::assertSame('CASE WHEN true THEN false ELSE NULL END', $when->stringify());
    }

    public function testMultipleWhen(): void
    {
        $when = new CaseWhen();
        $when->when(new Value(true), new Value(false));
        $when->when(new Value(false), new Value(true));
        $when->else(new Value(null));
        static::assertSame('CASE WHEN true THEN false WHEN false THEN true ELSE NULL END', $when->stringify());
    }

    public function testSwitchStyle(): void
    {
        $when = new CaseWhen(new Value(1));
        $when->when(new Value(1), new Value(false));
        $when->when(new Value(2), new Value(true));
        static::assertSame('CASE 1 WHEN 1 THEN false WHEN 2 THEN true END', $when->stringify());
    }

    public function testSwitchStyleElse(): void
    {
        $when = new CaseWhen(new Value(1));
        $when->when(new Value(1), new Value(false));
        $when->when(new Value(2), new Value(true));
        $when->else(new Value(null));
        static::assertSame('CASE 1 WHEN 1 THEN false WHEN 2 THEN true ELSE NULL END', $when->stringify());
    }
}
