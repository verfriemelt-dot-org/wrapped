<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\DI;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\DI\ContainerException;

class a
{
    public function __construct(public b $arg) {}
}

class b
{
    public function __construct(public string $instance = 'number 1') {}
}

class a_union
{
    public function __construct(public b_union|c_union $union) {}
}

class b_union
{
    public function __construct(public string $instance = 'default') {}
}

class c_union
{
    public function __construct(public string $instance = 'default') {}
}

class circle
{
    public function __construct(public circle $circle) {}
}

class circleA
{
    public function __construct(public circleB $circle) {}
}

class circleB
{
    public function __construct(public circleC $circle) {}
}

class circleC
{
    public function __construct(public circleA $circle) {}
}

interface i {}

class a_i implements i {}

class b_i implements i {}

class default_a
{
    public function __construct(public default_b $b = new default_b()) {}
}

class default_a_null
{
    public function __construct(public ?default_b $b = null) {}
}

class default_b
{
    public function __construct(public string $foo = 'foo') {}
}

class ContainerTest extends TestCase
{
    public function test_get_class(): void
    {
        $container = new Container();

        /* @phpstan-ignore-next-line */
        static::assertInstanceOf(a::class, $container->get(a::class));
    }

    public function test_should_reuse_instances_per_default(): void
    {
        $container = new Container();

        $b = new b('number 2');
        $container->register($b::class, $b);

        $result = $container->get(a::class);
        static::assertSame($result->arg->instance, $b->instance, 'instance must be reused');
    }

    public function test_do_not_reuse_on_when_configured(): void
    {
        $container = new Container();

        $b = new b('number 2');
        $container->register($b::class, $b)->share(false);

        $result = $container->get(a::class);
        static::assertNotSame($result->arg->instance, $b->instance, 'instance must not be reused');
    }

    public function test_should_throw_exception_on_circular_depedencies(): void
    {
        $this->expectExceptionMessage('circular');
        $container = new Container();

        $container->get(circleA::class);
    }

    public function test_get_instance_from_interface_when_registered(): void
    {
        $container = new Container();
        $container->register(a_i::class);

        static::assertTrue($container->get(i::class) instanceof a_i);
    }

    public function test_register_interface_with_instance(): void
    {
        $container = new Container();
        $container->register(i::class, new b_i());

        static::assertTrue($container->get(i::class) instanceof b_i);
    }

    public function test_empty_request(): void
    {
        $this->expectExceptionObject(new ContainerException('illegal'));

        /* @phpstan-ignore-next-line */
        (new Container())->get('');
    }

    public function test_paramconfiguration_overwrite(): void
    {
        $container = new Container();
        $container->register(a::class)
            ->parameter(b::class, fn (): b => new b('number 2'));

        $instance = $container->get(a::class);

        static::assertSame('number 2', $instance->arg->instance);
    }

    public function test_paramconfiguration_overwrite_with_argument_name(): void
    {
        $container = new Container();
        $container->register(a::class)
            ->parameter('arg', fn (): b => new b('number 2'));

        $instance = $container->get(a::class);

        static::assertSame('number 2', $instance->arg->instance);
    }

    public function test_paramconfiguration_overwrite_with_argument_type(): void
    {
        $container = new Container();
        $container->register(a::class)
            ->parameter(b::class, fn (): b => new b('number 2'));

        $instance = $container->get(a::class);

        static::assertSame('number 2', $instance->arg->instance);
    }

    public function test_paramconfiguration_overwrite_with_union_types(): void
    {
        $container = new Container();
        $container->register(a_union::class)
            ->parameter('union', fn (): b_union => new b_union('number 2'));

        $instance = $container->get(a_union::class);

        static::assertSame('number 2', $instance->union->instance);
    }

    public function test_providing_default_overrides(): void
    {
        $container = new Container();
        $container->register(
            default_a::class
        )->parameter(
            default_b::class,
            fn () => new default_b('bar')
        );

        $instance = $container->get(default_a::class);

        static::assertSame('bar', $instance->b->foo);
    }

    public function test_with_object_default(): void
    {
        $container = new Container();
        $instance = $container->get(default_a_null::class);

        static::assertNull($instance->b);
    }

    public function test_factory_with_function(): void
    {
        $container = new Container();
        $container->register(b::class, fn () => new b());

        static::assertSame(
            $container->get(b::class),
            $container->get(b::class),
            'factory should create single instance'
        );
    }

    public function test_factory_registration(): void
    {
        $container = new Container();
        $container->register(b::class, fn () => new b('factory'));

        $instance = $container->get(b::class);

        static::assertInstanceOf(b::class, $instance);
        static::assertSame('factory', $instance->instance);
    }

    public function test_factory_arguments(): void
    {
        static::expectNotToPerformAssertions();

        $container = new Container();
        $container->register(b::class, fn (a_i $a) => new b('factory'));
        $container->get(b::class);
    }

    public function test_factory_non_shareable(): void
    {
        $container = new Container();
        $container->register(b::class, fn () => new b('factory'))->share(false);

        $first = $container->get(b::class);
        $second = $container->get(b::class);

        static::assertNotSame($first, $second);
    }
}
