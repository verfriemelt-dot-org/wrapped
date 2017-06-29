<?php

    namespace Wrapped\_\Router;

    trait RouteIterator {

        /**
         *
         * @var Routable[]
         */
        public $routes = [];

        /**
         *
         * @return mixed
         */
        public function current() {
            return current( $this->routes );
        }

        /**
         *
         * @return mixed
         */
        public function key() {
            return key( $this->routes );
        }

        public function next() {
            next( $this->routes );
        }

        public function rewind() {
            reset( $this->routes );
        }

        /**
         *
         * @return bool
         */
        public function valid() {
            return isset( $this->routes[$this->key()] );
        }

    }
