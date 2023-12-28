<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Command
{
    public function __construct(
        public readonly string $command,
    ) {}
}
