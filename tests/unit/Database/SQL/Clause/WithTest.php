<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Clause\CTE;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;
use verfriemelt\wrapped\_\Database\SQL\Statement;

class WithTest extends TestCase
{
    public function test_minimal(): void
    {
        $cte = new CTE();
        $cte->with(new Identifier('test'), new Statement(new Select(new Value(true))));
        static::assertSame('WITH test AS ( SELECT true )', $cte->stringify());
    }

    public function test_multiple(): void
    {
        $cte = new CTE();
        $cte->with(new Identifier('test'), new Statement(new Select(new Value(true))));
        $cte->with(new Identifier('test2'), new Statement(new Select(new Value(null))));
        static::assertSame('WITH test AS ( SELECT true ), test2 AS ( SELECT NULL )', $cte->stringify());
    }
}
