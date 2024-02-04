<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Router;

use Closure;
use RuntimeException;
use Override;

final class RouteGroup implements Routable
{
    /** @var Routable[] */
    public array $routes = [];

    /** @var Closure[] */
    private array $filters = [];

    public function __construct(
        private string $path
    ) {}

    public static function create(string $path): self
    {
        return new self($path);
    }

    public function add(Routable ...$routes): self
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }

        return $this;
    }

    /**
     * @return Routable[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    #[Override]
    public function getPath(): string
    {
        return $this->path;
    }

    #[Override]
    public function setPath(string $prefix): self
    {
        $this->path = $prefix;
        return $this;
    }

    /**
     * @return Closure[]
     */
    #[Override]
    public function getFilters(): array
    {
        return $this->filters;
    }

    #[Override]
    public function addFilter(callable $filterFunc): self
    {
        $this->filters[] = $filterFunc;
        return $this;
    }

    public function current(): Routable
    {
        return current($this->routes) ?: throw new RuntimeException();
    }

    public function key(): mixed
    {
        return key($this->routes);
    }

    public function next(): void
    {
        next($this->routes);
    }

    public function rewind(): void
    {
        reset($this->routes);
    }

    public function valid(): bool
    {
        return isset($this->routes[$this->key()]);
    }
}
