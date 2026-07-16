<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use ReflectionAttribute;

class ArgumentMetadata
{
    private mixed $defaultValue;

    /**
     * @param array<class-string|string> $types
     * @param ReflectionAttribute[]      $attributes
     */
    public function __construct(
        private readonly string $name,
        private readonly array $types,
        private readonly bool $hasDefaultValue,
        mixed $defaultValue,
        private readonly bool $isVariadic,
        private readonly string $method,
        private readonly array $attributes,
    ) {
        if ($this->hasDefaultValue) {
            $this->defaultValue = $defaultValue;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMethodName(): string
    {
        return $this->method;
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
            throw new ArgumentResolverException(sprintf('Argument »%s« has no default value', $this->name));
        }

        return $this->defaultValue;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return T|null
     */
    public function findAttribute(string $attribute): ?object
    {
        foreach ($this->attributes as $reflectionAttribute) {
            assert($reflectionAttribute instanceof ReflectionAttribute);
            if ($reflectionAttribute->getName() === $attribute) {
                $instance = $reflectionAttribute->newInstance();
                assert($instance instanceof $attribute);

                return $instance;
            }
        }

        return null;
    }
}
