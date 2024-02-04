<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Serializer\Encoder;

use verfriemelt\wrapped\_\Serializer\Transformer;

interface EncoderInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function deserialize(string $input, string $class): object;

    public function serialize(object $input): string;

    public function addTransformer(Transformer $transformer): void;
}
