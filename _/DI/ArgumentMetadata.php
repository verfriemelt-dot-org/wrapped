<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use Exception;

class ArgumentMetadata
{
    private mixed $defaultValue;

    /**
     * @param array<class-string|string> $types
     */
    public function __construct(
        private readonly string $name,
        private readonly array $types,
        private readonly bool $hasDefaultValue = false,
        mixed $defaultValue = null,
        private readonly bool $isVariadic = false,
    ) {
        if ($this->hasDefaultValue) {
            $this->defaultValue = $defaultValue;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<class-string|string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }

    public function getDefaultValue(): mixed
    {
        if (!$this->hasDefaultValue) {
            throw new Exception(sprintf('Argument »%s« has no default value', $this->name));
        }

        return $this->defaultValue;
    }
}
