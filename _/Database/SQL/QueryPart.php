<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL;

    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\DataModel\DataModel;

    abstract class QueryPart {

        protected array $children = [];

        /**
         *
         * @var DataModel[]
         */
        protected array $context = [];

        abstract function stringify( DatabaseDriver $driver = null ): string;

        public function addDataModelContext( DataModel $context ) {

            if ( in_array( $context, $this->context ) ) {
                return $this;
            }


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
