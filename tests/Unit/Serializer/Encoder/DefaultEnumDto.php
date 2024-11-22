<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Serializer\Encoder;

final readonly class DefaultEnumDto
{
    public function __construct(
        public TestEnum $arg = TestEnum::Foo,
    ) {}
}
