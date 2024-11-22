<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Serializer\Encoder;

final readonly class VariadicDto
{
    /** @var SimpleDto[] */
    public array $subDtos;

    public function __construct(
        SimpleDto ...$subDtos,
    ) {
        $this->subDtos = $subDtos;
    }
}
