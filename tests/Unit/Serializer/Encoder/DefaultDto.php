<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Serializer\Encoder;

final readonly class DefaultDto
{
    public function __construct(
        public int $one = 1,
        public int $two = 2,
        public int $three = 3,
    ) {}
}
