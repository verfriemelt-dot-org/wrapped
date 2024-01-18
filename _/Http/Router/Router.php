<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Router;

use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Router\Exception\NoRouteMatching;
use verfriemelt\wrapped\_\Http\Router\Exception\NoRoutesPresent;

final class Router
{
    /** @var Routable[] */
    private array $routes = [];

    /** @var Route[] */
    private array $flattenedRoutes;

    public function add(Routable ...$routes): self
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }

        return $this;
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

        $this->flattenedRoutes ??= $this->flattenRoutes($this->routes);

        return $this->findMatchingRoute($this->flattenedRoutes, $uri);
    }

    /**
     * @param Route[] $routes
     */
    private function findMatchingRoute(array $routes, string $uri): Route
    {
        foreach ($routes as $route) {
            if (preg_match("~^{$route->getPath()}~", $uri, $routeHits, PREG_UNMATCHED_AS_NULL)) {
                $route->setAttributes(array_slice($routeHits, 1));
                return $route;
            }
        }

        throw new NoRouteMatching("Router has no matching routes for {$uri}");
    }
}
