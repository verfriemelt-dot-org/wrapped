<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli\Argument;

class Option
{
    private string $value;
    private bool $isPresent = false;

    public const REQUIRED = 0b1;
    public const EXPECTS_VALUE = 0b10;

    public function __construct(
        public readonly string $name,
        public readonly bool $optional = true,
        public readonly ?int $flags = 0,
        public readonly ?string $description = null,
        public readonly ?string $short = null,
    ) {
    }

    public function setValue(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function markPresent(bool $bool = true): static
    {
        $this->isPresent = $bool;
        return $this;
    }

    public function isPresent(): bool
    {
        return $this->isPresent;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isValueRequired(): bool
    {
        return ($this->flags & self::EXPECTS_VALUE) === self::EXPECTS_VALUE;
    }
}
