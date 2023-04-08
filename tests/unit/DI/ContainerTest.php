<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\DI\ContainerException;

class a
{
    public function __construct(public b $b)
    {
    }
}

class b
{
    public function __construct(public string $instance = 'number 1')
    {
    }
}

class circle
{
    public function __construct(public circle $circle)
    {
    }
}

class circleA
{
    public function __construct(public circleB $circle)
    {
    }
}

class circleB
{
    public function __construct(public circleC $circle)
    {
    }
}

class circleC
{
    public function __construct(public circleA $circle)
    {
    }
}

interface i
{
}

class a_i implements i
{
}

class b_i implements i
{
}

class ContainerTest extends TestCase
{
    public function testGetClass(): void
    {
        $container = new Container();

        /* @phpstan-ignore-next-line */
        static::assertInstanceOf(a::class, $container->get(a::class));
    }

    public function testShouldReuseInstancesPerDefault(): void
    {
        $container = new Container();

        $b = new b('number 2');
        $container->register($b::class, $b);

        $result = $container->get(a::class);
        static::assertSame($result->b->instance, $b->instance, 'instance must be reused');
    }

    public function testDoNotReuseOnWhenConfigured(): void
    {
        $container = new Container();

        $b = new b('number 2');
        $container->register($b::class, $b)->share(false);

        $result = $container->get(a::class);
        static::assertNotSame($result->b->instance, $b->instance, 'instance must not be reused');
    }

    public function testShouldThrowExceptionOnCircularDepedencies(): void
    {
        $this->expectExceptionMessage('circular');
        $container = new Container();

        $container->get(circleA::class);
    }

    public function testGetInstanceFromInterfaceWhenRegistered(): void
    {
        $container = new Container();
        $container->register(a_i::class);

        static::assertTrue($container->get(i::class) instanceof a_i);
    }

    public function testRegisterInterfaceWithInstance(): void
    {
        $container = new Container();
        $container->register(i::class, new b_i());

        static::assertTrue($container->get(i::class) instanceof b_i);
    }

    public function testEmptyRequest(): void
    {
        $this->expectExceptionObject(new ContainerException('illegal'));

        /* @phpstan-ignore-next-line */
        (new Container())->get('');
    }
}
