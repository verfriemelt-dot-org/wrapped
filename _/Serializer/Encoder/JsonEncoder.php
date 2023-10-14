<?php

declare(strict_types=1);

namespace _\Serializer\Encoder;

use stdClass;

class JsonEncoder implements EncoderInterface
{
    public function __construct() {}

    public function deserialize(string $input, string $class): object
    {
        return new stdClass();
    }

    public function serialze(object $input): string
    {
        return \json_encode($input, JSON_THROW_ON_ERROR);
    }
}
