<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Database\SQL;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class AliasableTest extends TestCase
{
    public function test_primitive(): void
    {
        $primitive = new Value(true);
        $primitive->addAlias(new Identifier('testing'));

        static::assertSame('true AS testing', $primitive->stringify());
    }
}
