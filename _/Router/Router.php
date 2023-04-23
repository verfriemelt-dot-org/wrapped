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

    /**
     * used for adding multiple routes
     *
     * @param Routeable $routes
     *
     * @return Router
     *
     * @deprecated since version number
     */
    public function addRoutes(Routable ...$routes)
    {
        return $this->add(...$routes);
    }

    /**
     * adding routes to the router
     *
     * @param type $routes
     *
     * @return $this
     */
    public function add(Routable ...$routes): self
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }

        return $this;
    }

    private function isFiltered($filterFunction): bool
    {
        if (!is_callable($filterFunction)) {
            return false;
        }

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

    public function flattenRoutes($routes, $prefix = '', $filters = [])
    {
        $flattened = [];

        foreach ($routes as $routeable) {
            $routeable->setPath($prefix . $routeable->getPath());

            array_map(fn ($c) => $routeable->addFilter($c), $filters);

            if ($routeable instanceof Route) {
                $flattened[] = $routeable;
                continue;
            }

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

        $this->sortRoutes($this->routes);
        $this->routes = $this->flattenRoutes($this->routes);

        return $this->findMatchingRoute($uri);
    }

    protected function findMatchingRoute($uri): Route
    {
        foreach ($this->routes as $route) {
            if (preg_match("~^{$route->getPath()}~", (string) $uri, $routeHits, PREG_UNMATCHED_AS_NULL)) {
                $route->setAttributes(array_slice($routeHits, 1));
                return $route;
            }
        }

        throw new NoRouteMatching("Router has no matching routes for {$uri}");
    }

    /**
     * sort routes according to priority
     */
    private function sortRoutes(&$routes)
    {
        usort($routes, fn (Routable $a, Routable $b) => $a->getPriority() <=> $b->getPriority());
    }

    /**
     * runs a filter before matching any routes
     *
     * @return $this
     */
    public function addGlobalFilter(callable $filter)
    {
        $this->globalFilter[] = $filter;
        return $this;
    }
}
