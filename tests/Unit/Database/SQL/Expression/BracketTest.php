<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Database\SQL\Expression;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Expression\Bracket;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class BracketTest extends TestCase
{
    public function test_wrapping(): void
    {
        $bracket = new Bracket();
        $bracket->add(
            new Value(true)
        );

        static::assertSame('( true )', $bracket->stringify());
    }
}
