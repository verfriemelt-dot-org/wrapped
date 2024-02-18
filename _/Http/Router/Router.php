<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Router;

use verfriemelt\wrapped\_\DI\ArgumentResolver;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Response;
use verfriemelt\wrapped\_\Http\Router\Exception\NoRouteMatching;
use verfriemelt\wrapped\_\Http\Router\Exception\RouteGotFiltered;

final class Router
{
    /** @var Routable[] */
    private array $routes = [];

    /** @var Route[] */
    private array $flattenedRoutes;

    public function __construct(
        private readonly ArgumentResolver $argumentResolver,
    ) {}

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

    public function handleRequest(Request $request): Route
    {
        $this->flattenedRoutes ??= $this->flattenRoutes($this->routes);

        $route = $this->findMatchingRoute($this->flattenedRoutes, $request);

        foreach ($route->getAttributes() as $key => $value) {
            $request->attributes()->override($key, $value);
        }

        $this->checkRouterFilter($route);

        return $route;
    }

    /**
     * @throws RouteGotFiltered
     */
    protected function checkRouterFilter(Route $route): void
    {
        // router filter
        foreach ($route->getFilters() as $filter) {
            $result = $filter(...$this->argumentResolver->resolv($filter));

            if ($result !== false) {
                $exception = new RouteGotFiltered();

                if ($result instanceof Response) {
                    $exception->setResponse($result);
                }

                throw $exception;
            }
        }
    }

    /**
     * @return Route[]
     */
    public function dumpRoutes(): array
    {
        return $this->flattenedRoutes ??= $this->flattenRoutes($this->routes);
    }

    /**
     * @param Route[] $routes
     */
    private function findMatchingRoute(array $routes, Request $request): Route
    {
        $uri = $request->uri();
        assert(\is_string($uri));

        foreach ($routes as $route) {
            if (preg_match("~^{$route->getPath()}~", $uri, $routeHits, PREG_UNMATCHED_AS_NULL)) {
                $route->setAttributes(array_slice($routeHits, 1));
                return $route;
            }
        }

        throw new NoRouteMatching("Router has no matching routes for {$uri}");
    }
}
