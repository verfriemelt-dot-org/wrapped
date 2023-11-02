<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Serializer\Encoder;

final readonly class NullDto
{
    public function __construct(
        public ?int $null,
    ) {}
}
