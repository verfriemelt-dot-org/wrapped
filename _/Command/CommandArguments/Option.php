<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command\CommandArguments;

final class Option
{
    final public const int OPTIONAL = 0b00000000;
    final public const int REQUIRED = 0b00000001;
    final public const int EXPECTS_VALUE = 0b00000010;

    private bool $isPresent = false;
    private ?string $value = null;

    public function __construct(
        public readonly string $name,
        public readonly int $flags = self::OPTIONAL,
        public readonly ?string $description = null,
        public readonly ?string $short = null,
    ) {}

    public function required(): bool
    {
        return ($this->flags & self::REQUIRED) === self::REQUIRED;
    }

    public function isValueRequired(): bool
    {
        return ($this->flags & self::EXPECTS_VALUE) === self::EXPECTS_VALUE;
    }

    public function markPresent(): void
    {
        $this->isPresent = true;
    }

    public function present(): bool
    {
        return $this->isPresent;
    }

    public function set(string $value): void
    {
        $this->markPresent();
        $this->value = $value;
    }

    public function get(): ?string
    {
        return $this->value;
    }
}
