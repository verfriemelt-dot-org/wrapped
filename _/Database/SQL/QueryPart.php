<?php

    namespace Wrapped\_\Database\SQL;

    abstract class QueryPart {

        protected array $children = [];

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

    }
