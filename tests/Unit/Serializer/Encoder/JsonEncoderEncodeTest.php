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

        static::assertSame('{"foo":"foo","bar":1}', $encoder->serialze($dto));
    }
}
