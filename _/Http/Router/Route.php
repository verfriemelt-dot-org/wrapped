<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Router;

use verfriemelt\wrapped\_\Http\Response\Response;
use Override;

class Route implements Routable
{
    private string $path;

    private $callback;

    private array $filters = [];

    protected array $attributes = [];

    public function __construct(string $path)
    {
        $this->setPath($path);
    }

    public static function create(string $path): static
    {
        return new self($path);
    }

    #[Override]
    public function getPath(): string
    {
        return $this->path;
    }

    public function getCallback(): object|callable|string
    {
        return $this->callback;
    }

    #[Override]
    public function getFilters(): array
    {
        return $this->filters;
    }

    #[Override]
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
    #[Override]
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
