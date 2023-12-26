<?php

declare(strict_types = 1);

namespace verfriemelt\wrapped\_\Command\CommandArguments;

final readonly class Argument
{
    final public const REQUIRED = 0b1;

    public function __construct(
        public string $name,
        public int $flags = 0,
        public ?string $description = null,
    )
    {
    }

    public function required(): bool
    {
        return ($this->flags & self::REQUIRED) === self::REQUIRED;
    }
}
