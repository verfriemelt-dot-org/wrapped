<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Serializer;

use verfriemelt\wrapped\_\Serializer\Encoder\EncoderInterface;

class Serializer
{
    public function __construct(
        private readonly EncoderInterface $encoder
    ) {}

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function deserialize(string $input, string $class): object
    {
        return $this->encoder->deserialize($input, $class);
    }

    public function serialze(object $input): string
    {
        return $this->encoder->serialze($input);
    }
}
