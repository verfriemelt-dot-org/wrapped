<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli\Argument;

class Option
{
    public function __construct(
        public readonly string $name,
        public readonly bool $optional = true,
        public readonly ?string $description = null,
        public readonly ?string $short = null,
    ) {
    }
}
