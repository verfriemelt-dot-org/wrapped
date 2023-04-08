<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Router;

class RouteGroup implements Routable
{
    use RouteIterator;

    private $path;

    private $priority = 100;

    private array $filters = [];

    public static function create(string $path): static
    {
        return new self($path);
    }

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function add(Routable ...$routes): static
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }

        return $this;
    }

    /** @return Route[] */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPath($prefix): static
    {
        $this->path = $prefix;
        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function addFilter(callable $filterFunc): static
    {
        $this->filters[] = $filterFunc;
        return $this;
    }
}
