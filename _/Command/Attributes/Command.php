<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Command
{
    public function __construct(
        public string $command,
        public string $description = '',
    ) {}
}
