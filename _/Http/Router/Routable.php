<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Router;

use Closure;

interface Routable
{
    /**
     * should return set path for the route;
     * could be an reguluar expression eq /admin/site/(?<id>[0-9])
     */
    public function getPath(): string;

    /**
     * @return Closure[]
     *
     * returns the filter functions for the route.
     * if that function returns true, the requests get filtered and
     * the callback wont be executed
     */
    public function getFilters(): array;

    public function setPath(string $path): self;

    public function addFilter(callable $filter): self;
}
