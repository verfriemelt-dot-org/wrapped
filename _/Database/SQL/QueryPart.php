<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\Driver\DatabaseDriver;

    abstract class QueryPart {

        protected array $children = [];

        /**
         *
         * @var \Wrapped\_\DataModel\DataModel[]
         */
        protected array $context = [];

        abstract function stringify( DatabaseDriver $driver = null ): string;

        public function addDataModelContext( \Wrapped\_\DataModel\DataModel $context ) {

            $this->context[] = $context;

            // attach context to every child
            array_map( fn( $child ) => $child->addDataModelContext( $context ), $this->children );


            return $this;
        }

        protected function addChild( QueryPart $child ) {

            $this->children[] = $child;

            // add context to children;
            array_map( fn( $context ) => $child->addDataModelContext( $context ), $this->context );

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
