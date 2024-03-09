<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Template\v2;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Token\Token;

class TokenBagTest extends TestCase
{
    public function test_children(): void
    {
        $bag = new Token();
        $childA = new Token();
        $childB = new Token();

        $bag->addChildren($childA);
        $bag->addChildren($childB);

        static::assertSame([$childA, $childB], $bag->children());
    }

    public function test_empty_children(): void
    {
        $bag = new Token();
        static::assertSame([], $bag->children());
    }

    public function test_parent(): void
    {
        $bag = new Token();
        $parent = new Token();

        $bag->setParent($parent);

        static::assertSame($parent, $bag->parent());
    }

    public function test_next(): void
    {
        $bag = new Token();
        $next = new Token();

        $bag->setNext($next);

        static::assertSame($next, $bag->next());
    }

    public function test_previous(): void
    {
        $bag = new Token();
        $previous = new Token();

        $bag->setPrevious($previous);

        static::assertSame($previous, $bag->previous());
    }
}
