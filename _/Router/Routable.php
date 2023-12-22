<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Router;

interface Routable
{
    /**
     * should return set path for the route;
     * could be an reguluar expression eq /admin/site/(?<id>[0-9])
     */
    public function getPath(): string;

    /**
     * defaults to 10; priorioty while 1 beeing more importatnt
     */
    public function getPriority(): int;

    /**
     * returns the filter functions for the route.
     * if that function returns true, the requests get filtered and
     * the callback wont be executed
     */
    public function getFilters();

    public function setPath(string $path): static;

    public function addFilter(callable $filter): static;
}
