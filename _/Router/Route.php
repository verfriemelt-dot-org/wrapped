<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Router;

use verfriemelt\wrapped\_\Http\Response\Response;

class Route implements Routable
{
    private string $path;

    private $callback;

    private array $filters = [];

    private int $priority = 100;

    protected array $attributes = [];

    public function __construct(string $path)
    {
        $this->setPath($path);
    }

    public static function create(string $path): static
    {
        return new self($path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $prio): static
    {
        $this->priority = $prio;
        return $this;
    }

    public function getCallback(): object|callable|string
    {
        return $this->callback;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setPath($path): static
    {
        $this->path = $path;
        return $this;
    }

    public function call(callable|Response|string $callback): static
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * if this function returns true no callback is triggered
     */
    public function addFilter(callable $filter): static
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
