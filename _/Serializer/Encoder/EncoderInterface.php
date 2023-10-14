<?php

declare(strict_types=1);

namespace _\Serializer\Encoder;

interface EncoderInterface
{
    public function deserialize(string $input, string $class): object;

    public function serialze(object $input): string;
}
