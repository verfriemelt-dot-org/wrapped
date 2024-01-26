<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Serializer\Encoder;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Serializer\Encoder\JsonEncoder;

class JsonEncoderDecodeTest extends TestCase
{
    public function test_simple_decode(): void
    {
        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize('{"foo":"bar","bar":2}', SimpleDto::class);

        static::assertSame('bar', $dto->foo);
        static::assertSame(2, $dto->bar);
    }

    public function test_decode_omitting_defaults(): void
    {
        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize('{"foo":"bar"}', SimpleDto::class);

        static::assertSame('bar', $dto->foo);
        static::assertSame(1, $dto->bar);
    }

    public function test_decode_all_defaults(): void
    {
        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize('{}', DefaultDto::class);

        static::assertSame(1, $dto->one);
        static::assertSame(2, $dto->two);
        static::assertSame(3, $dto->three);
    }

    public function test_decode_skipping_defaults(): void
    {
        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize('{"one":5, "three": 8}', DefaultDto::class);

        static::assertSame(5, $dto->one);
        static::assertSame(2, $dto->two);
        static::assertSame(8, $dto->three);
    }

    public function test_ignoring_extra(): void
    {
        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize('{"foo":"bar","nope": true}', SimpleDto::class);

        static::assertSame('bar', $dto->foo);
        static::assertSame(1, $dto->bar);
        static::assertObjectNotHasProperty('nope', $dto);
    }

    public function test_nested_dtos(): void
    {
        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize('{"foo":"bar","subDto":{"foo":"bar"}}', NestedDto::class);

        static::assertSame('bar', $dto->foo);
        static::assertInstanceOf(SimpleDto::class, $dto->subDto);
        static::assertSame('bar', $dto->subDto->foo);
    }

    public function test_decode_null(): void
    {
        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize('{"null":null}', NullDto::class);

        static::assertNull($dto->null);
    }

    public function test_variadic(): void
    {
        $input = <<<JSON
        {
            "variadic": [
                {
                    "foo": "foo",
                    "bar": 1
                },
                {
                    "foo": "foo",
                    "bar": 2
                },
                {
                    "foo": "foo",
                    "bar": 3
                }
            ]
        }
        JSON;

        $class = new class () {
            public function __construct(
                public readonly ?VariadicDto $variadic = null
            ) {}
        };

        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize($input, $class::class);

        static::assertInstanceOf($class::class, $dto);
        static::assertCount(3, $dto->variadic->subDtos ?? []);
    }

    public function test_simple_enum_decode(): void
    {
        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize('{"arg": "bar"}', SimpleEnumDto::class);

        static::assertSame(TestEnum::Bar, $dto->arg);
    }

    public function test_default_enum_decode(): void
    {
        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize('{}', DefaultEnumDto::class);

        static::assertSame(TestEnum::Foo->value, $dto->arg->value);
    }

    public function test_arry_of_string(): void
    {
        $input = <<<JSON
        [
           "foo1",
           "foo2",
           "foo3"
        ]
        JSON;

        $class = new class ('') {
            public array $variadic = [];

            public function __construct(
                string ... $variadic
            ) {
                $this->variadic = $variadic;
            }
        };

        $encoder = new JsonEncoder();
        $dto = $encoder->deserialize($input, $class::class);

        static::assertInstanceOf($class::class, $dto);
        static::assertSame(['foo1', 'foo2', 'foo3'], $dto->variadic);
    }
}
