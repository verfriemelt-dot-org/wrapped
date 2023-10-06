<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli\Argument;

class Argument
{
    private string $value;

    public function __construct(
        public readonly string $name,
        public readonly bool $optional = false,
        public readonly ?string $description = null,
    ) {}

    public function setValue(string $input): self
    {
        $this->value = $input;
        return $this;
    }

    public function isInitialized(): bool
    {
        return isset($this->value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isPresent(): bool
    {
        return $this->isInitialized();
    }
}
