<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Router;

trait RouteIterator
{
    /**
     * @var Routable[]
     */
    public $routes = [];

    public function current()
    {
        return current($this->routes);
    }

    public function key()
    {
        return key($this->routes);
    }

    public function next()
    {
        next($this->routes);
    }

    public function rewind()
    {
        reset($this->routes);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->routes[$this->key()]);
    }
}
