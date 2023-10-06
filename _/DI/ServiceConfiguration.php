<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use Closure;
use Exception;

/**
 * @template T of object
 */
class ServiceConfiguration
{
    private bool $shareable = true;

    private readonly string $id;

    /** @var class-string<T> */
    private string $class;

    /** @var array<string|class-string, Closure> */
    private array $resolver = [];

    /**
     * @param class-string<T> $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;

        if (class_exists($id)) {
            $this->setClass($id);
        }
    }

    public function share(bool $bool = true): static
    {
        $this->shareable = $bool;
        return $this;
    }

    public function isShareable(): bool
    {
        return $this->shareable;
    }

    /**
     * @param class-string<T> $class
     *
     * @throws Exception
     */
    public function setClass(string $class): static
    {
        if (!class_exists($class)) {
            throw new Exception(sprintf('unkown class: »%s«', $class));
        }

        $this->class = $class;
        return $this;
    }

    /**
     * @return class-string<T>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return class-string[]
     */
    public function getInterfaces(): array
    {
        /** @var class-string[] $interfaces */
        $interfaces = class_implements($this->class);

        return $interfaces;
    }

    /**
     * @param string|class-string $parameter
     */
    public function parameter(string $parameter, Closure $resolver): static
    {
        $this->resolver[$parameter] = $resolver;
        return $this;
    }

    /**
     * @param string|class-string $parameter
     */
    public function hasParameter(string $parameter): bool
    {
        return isset($this->resolver[$parameter]);
    }

    /**
     * @param string|class-string $parameter
     */
    public function getResolver(string $parameter): Closure
    {
        return $this->resolver[$parameter];
    }
}
