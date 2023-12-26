<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command\CommandArguments;

final readonly class Option
{
    final public const REQUIRED = 0b1;
    final public const EXPECTS_VALUE = 0b10;

    public function __construct(
        public string $name,
        public int $flags = 0b00,
        public ?string $description = null,
        public ?string $short = null,
    ) {}

    public function required(): bool
    {
        return ($this->flags & self::REQUIRED) === self::REQUIRED;
    }

    public function isValueRequired(): bool
    {
        return ($this->flags & self::EXPECTS_VALUE) === self::EXPECTS_VALUE;
    }
}
