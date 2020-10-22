<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\Driver\DatabaseDriver;

    abstract class QueryPart {

        protected array $children = [];

        abstract function stringify( DatabaseDriver $driver = null ): string;

        protected function addChild( QueryPart $child ) {
            $this->children[] = $child;
            return $this;
        }

        public function getChildren() {
            return $this->children;
        }

        public function fetchBindings() {
            return array_merge( [], ... array_map( fn( $child ) => $child->fetchBindings(), $this->children ) );
        }

        public function fetchAllChildren() {

            return array_merge( [ $this ], ... array_map( fn( $child ) => $child->fetchAllChildren(), $this->children ) );
        }

    }
