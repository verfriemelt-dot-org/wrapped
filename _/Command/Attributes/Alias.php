<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Alias
{
    public function __construct(
        public string $alias
    ) {}
}
