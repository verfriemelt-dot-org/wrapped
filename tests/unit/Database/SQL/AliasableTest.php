<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;

class AliasableTest extends TestCase
{
    public function testPrimitive(): void
    {
        $primitive = new Value(true);
        $primitive->addAlias(new Identifier('testing'));

        static::assertSame('true AS testing', $primitive->stringify());
    }
}
