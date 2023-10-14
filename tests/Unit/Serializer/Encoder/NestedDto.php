<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Serializer\Encoder;

final readonly class NestedDto
{
    public function __construct(
        public string $foo,
        public SimpleDto $subDto,
    ) {}
}
