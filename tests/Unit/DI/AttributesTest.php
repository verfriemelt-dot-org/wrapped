<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\DI;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\ArgumentResolverException;
use verfriemelt\wrapped\_\DI\Container;

class AttributesTest extends TestCase
{
    public function test_attribute_default_overridden(): void
    {

        $_ENV['FOO_BAR'] = 'expected';

        $container = new Container();
        $instance = $container->get(test_attribute_default_attribute::class);
        static::assertSame('expected', $instance->test);

        unset($_ENV['FOO_BAR']);
    }

    public function test_attribute_default(): void
    {
        $container = new Container();
        $instance = $container->get(test_attribute_default_attribute::class);
        static::assertSame('default_attribute', $instance->test);
    }

    public function test_without_default(): void
    {
        static::expectException(ArgumentResolverException::class);
        $container = new Container();
        $instance = $container->get(test_attribute_without_default::class);
    }

    public function test_default_on_argument_overridden(): void
    {

        $_ENV['FOO_BAR'] = 'overridden';

        $container = new Container();
        $instance = $container->get(test_attribute_without_default::class);
        static::assertSame('overridden', $instance->test);

        unset($_ENV['FOO_BAR']);
    }

    public function test_with_default_on_argument(): void
    {
        $container = new Container();
        $instance = $container->get(test_attribute_default_argument::class);
        static::assertSame('default_argument', $instance->test);
    }

    public function test_with_default_on_argument_overridden(): void
    {

        $_ENV['FOO_BAR'] = 'overridden';

        $container = new Container();
        $instance = $container->get(test_attribute_default_argument::class);
        static::assertSame('overridden', $instance->test);

        unset($_ENV['FOO_BAR']);
    }

    public function test_with_default_on_both(): void
    {
        $container = new Container();
        $instance = $container->get(test_attribute_default_both::class);
        static::assertSame('default_attribute', $instance->test);
    }

    public function test_with_default_on_both_overridden(): void
    {

        $_ENV['FOO_BAR'] = 'overridden';

        $container = new Container();
        $instance = $container->get(test_attribute_default_both::class);
        static::assertSame('overridden', $instance->test);

        unset($_ENV['FOO_BAR']);
    }
}


class test_attribute_default_attribute
{
    public function __construct(
        #[\verfriemelt\wrapped\_\DI\Attributes\Env('FOO_BAR', 'default_attribute')]
        public readonly string $test,
    ) {}
}

class test_attribute_without_default
{
    public function __construct(
        #[\verfriemelt\wrapped\_\DI\Attributes\Env('FOO_BAR')]
        public readonly string $test,
    ) {}
}


class test_attribute_default_argument
{
    public function __construct(
        #[\verfriemelt\wrapped\_\DI\Attributes\Env('FOO_BAR')]
        public readonly string $test = 'default_argument',
    ) {}
}

class test_attribute_default_both
{
    public function __construct(
        #[\verfriemelt\wrapped\_\DI\Attributes\Env('FOO_BAR', 'default_attribute')]
        public readonly string $test = 'default_argument',
    ) {}
}
