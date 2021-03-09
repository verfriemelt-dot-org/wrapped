<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Router;

    use \Iterator;

    class RouteGroup
    implements Iterator, Routable {

        use RouteIterator;

        private $prefix;

        private $priority = 100;

        private $filterCallback;

        /**
         * create new RouteGroup for chaining
         * @param type $path
         * @return \static
         */
        public static function create( $path ) {
            return new self( $path );
        }

        public function __construct( $prefix ) {
            $this->prefix = $prefix;
        }

        /**
         *
         * @param Route $route
         * @return RouteGroup
         */
        public function add( Routable ... $routes ) {

            foreach ( $routes as $route ) {
                $this->routes[] = $route;
            }

            return $this;
        }

        /**
         *
         * @return Route[]
         */
        public function getRoutes() {
            return $this->routes;
        }

        public function getPrefix() {
            return $this->prefix;
        }

        public function getPath() {
            return $this->getPrefix();
        }

        public function getPriority() {
            return $this->priority;
        }

        /**
         *
         * @param type $prefix
         * @return RouteGroup
         */
        public function setPrefix( $prefix ) {
            $this->prefix = $prefix;
            return $this;
        }

        public function getFilterCallback() {
            return $this->filterCallback;
        }

        /**
         *
         * @param \verfriemelt\wrapped\_\Router\callable $filterFunc
         * @return RouteGroup
         */
        public function setFilterCallback( callable $filterFunc ) {
            $this->filterCallback = $filterFunc;
            return $this;
        }

    }
