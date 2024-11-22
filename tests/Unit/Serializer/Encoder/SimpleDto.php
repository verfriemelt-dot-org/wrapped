<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Serializer\Encoder;

final readonly class SimpleDto
{
    public function __construct(
        public string $foo,
        public int $bar = 1,
    ) {}
}
