<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use Exception;
use RuntimeException;

class ArgumentMetadata
{
    private string $name;

    /**
     * @var class-string|null
     */
    private ?string $type = null;

    private bool $hasDefaultValue;

    private mixed $defaultValue;

    /**
     * @param class-string|null $type
     * @param mixed             $defaultValue
     */
    public function __construct(string $name, ?string $type, bool $hasDefaultValue = false, mixed $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->hasDefaultValue = $hasDefaultValue;

        if ($this->hasDefaultValue) {
            $this->defaultValue = $defaultValue;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return class-string
     */
    public function getType(): string
    {
        if ($this->type === null) {
            throw new RuntimeException('nope');
        }

        return $this->type;
    }

    public function hasType(): bool
    {
        return $this->type !== null;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function getDefaultValue(): mixed
    {
        if (!$this->hasDefaultValue) {
            throw new Exception(sprintf('Argument »%s« has no default value', $this->name));
        }

        return $this->defaultValue;
    }
}
