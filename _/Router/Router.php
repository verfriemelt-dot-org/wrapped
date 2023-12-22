<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Router;

use verfriemelt\wrapped\_\Exception\Router\NoRouteMatching;
use verfriemelt\wrapped\_\Exception\Router\NoRoutesPresent;
use verfriemelt\wrapped\_\Exception\Router\RouteGotFiltered;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Response;

final class Router
{
    use RouteIterator;

    private readonly Request $request;

    private array $globalFilter = [];

    public function add(Routable ...$routes): self
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }

        return $this;
    }

    private function isFiltered(callable $filterFunction): bool
    {
        $functionResult = $filterFunction($this->request);

        if ($functionResult === false) {
            return false;
        }

        $filterException = new RouteGotFiltered('route got filtered');

        if ($functionResult instanceof Response) {
            $filterException->setResponse($functionResult);
        }

        throw $filterException;
    }

    /**
     * @param Routable[] $routes
     * @param callable[] $filters
     *
     * @return Route[]
     */
    private function flattenRoutes(array $routes, string $prefix = '', array $filters = []): array
    {
        $flattened = [];

        foreach ($routes as $routeable) {
            $routeable->setPath($prefix . $routeable->getPath());

            array_map(fn (callable $c) => $routeable->addFilter($c), $filters);

            if ($routeable instanceof Route) {
                $flattened[] = $routeable;
                continue;
            }

            assert($routeable instanceof RouteGroup);

            // routegroup
            $flattened = [
                ...$flattened,
                ...$this->flattenRoutes(
                    $routeable->getRoutes(),
                    $routeable->getPath(),
                    [...$filters, ...$routeable->getFilters()]
                ),
            ];
        }

        return $flattened;
    }

    public function handleRequest(string|Request $uri): Route
    {
        if ($uri instanceof Request) {
            $uri = $uri->uri();
        }

        if (empty($this->routes)) {
            throw new NoRoutesPresent('Router is empty');
        }

        \usort($this->routes, fn (Routable $a, Routable $b) => $a->getPriority() <=> $b->getPriority());
        $this->routes = $this->flattenRoutes($this->routes);

        return $this->findMatchingRoute($uri);
    }

    protected function findMatchingRoute(string $uri): Route
    {
        foreach ($this->routes as $route) {
            if (preg_match("~^{$route->getPath()}~", $uri, $routeHits, PREG_UNMATCHED_AS_NULL)) {
                $route->setAttributes(array_slice($routeHits, 1));
                return $route;
            }
        }

        throw new NoRouteMatching("Router has no matching routes for {$uri}");
    }

    public function addGlobalFilter(callable $filter): self
    {
        $this->globalFilter[] = $filter;
        return $this;
    }
}
