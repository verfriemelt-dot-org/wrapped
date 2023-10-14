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
}
