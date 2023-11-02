<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Serializer\Encoder;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Serializer\Encoder\JsonEncoder;

class JsonEncoderEncodeTest extends TestCase
{
    public function test_encode(): void
    {
        $dto = new SimpleDto('foo', 1);
        $encoder = new JsonEncoder();

        static::assertSame('{"foo":"foo","bar":1}', $encoder->serialize($dto));
    }

    public function test_encode_nested(): void
    {
        $dto = new NestedDto(
            'bar',
            new SimpleDto('foo', 1)
        );
        $encoder = new JsonEncoder();

        static::assertSame('{"foo":"bar","subDto":{"foo":"foo","bar":1}}', $encoder->serialize($dto));
    }

    public function test_encode_null(): void
    {
        $encoder = new JsonEncoder();
        static::assertSame('{"null":null}', $encoder->serialize(new \verfriemelt\wrapped\tests\Unit\Serializer\Encoder\NullDto(null)));
    }

    public function test_variadic(): void
    {
        $expected = <<<JSON
        {
            "subDtos": [
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

        $dto = new VariadicDto(
            new SimpleDto('foo', 1),
            new SimpleDto('foo', 2),
            new SimpleDto('foo', 3),
        );

        $encoder = new JsonEncoder();
        static::assertSame($expected, $encoder->serialize($dto, true));
    }
}
