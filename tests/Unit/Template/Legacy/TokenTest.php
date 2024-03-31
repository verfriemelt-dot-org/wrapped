<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Template\v2;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Token\RootToken;
use verfriemelt\wrapped\_\Template\Token\StringToken;

class TokenTest extends TestCase
{
    public function test_children(): void
    {
        $root = new RootToken();
        $childA = new StringToken();
        $childB = new StringToken();

        $root->addChildren($childA);
        $root->addChildren($childB);

        static::assertSame([$childA, $childB], $root->children());
    }

    public function test_empty_children(): void
    {
        $root = new RootToken();
        static::assertSame([], $root->children());
    }

    public function test_parent(): void
    {
        $root = new RootToken();
        $parent = new StringToken();

        $root->setParent($parent);

        static::assertSame($parent, $root->parent());
    }

    public function test_next(): void
    {
        $root = new StringToken();
        $next = new StringToken();

        $root->setNext($next);

        static::assertSame($next, $root->next());
    }

    public function test_previous(): void
    {
        $root = new StringToken();
        $previous = new StringToken();

        $root->setPrevious($previous);

        static::assertSame($previous, $root->previous());
    }
}
