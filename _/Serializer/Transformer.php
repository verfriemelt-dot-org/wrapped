<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Serializer;

interface Transformer
{
    /**
     * @param array<string,mixed> $input
     */
    public function supports(array $input): bool;

    /**
     * @param array<string,mixed> $input
     *
     * @return array<string,mixed>
     */
    public function transform(array $input): array;
}
