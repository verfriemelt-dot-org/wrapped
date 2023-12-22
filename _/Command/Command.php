<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Command
{
    public function __construct(
        public readonly string $name,
    ) {}
}
