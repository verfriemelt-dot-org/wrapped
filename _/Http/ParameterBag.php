<?php

    namespace Wrapped\_\Http;

    use \ArrayIterator;
    use \Countable;
    use \IteratorAggregate;

    class ParameterBag
    implements Countable, IteratorAggregate {

        private $parameters = [];

        public function __construct( array $parameters ) {
            $this->parameters = $parameters;
        }

        public function count() {
            return count( $this->parameters );
        }

        /**
         *
         * @return ArrayIterator
         */
        public function getIterator() {
            return new ArrayIterator( $this->parameters );
        }

        public function hasNot( $key ) {
            return !$this->has( $key );
        }

        public function has( $key ) {
            return isset( $this->parameters[$key] );
        }

        public function get( $key, $default = null ) {

            if ( !$this->has( $key ) ) {
                return $default;
            }

            return $this->parameters[$key];
        }

        public function is( $key, $value ) {
            return $this->get( $key ) == $value;
        }

        public function isNot( $key, $value ) {
            return $this->get( $key ) != $value;
        }

        public function all() {
            return $this->parameters;
        }

        public function first() {
            reset( $this->parameters );
            return current( $this->parameters );
        }

        public function last() {
            end( $this->parameters );
            return current( $this->parameters );
        }

        public function except( array $filter = [] ) {

            $return = [];

            foreach ( $this->all() as $key => $value ) {
                if ( !in_array( $key, $filter ) ) {
                    $return[$key] = $value;
                }
            }

            return $return;
        }

        /**
         * overrides key in the given bag
         * @param type $key
         * @param type $value
         * @return ParameterBag
         */
        public function override( $key, $value ) {
            $this->parameters[$key] = $value;
            return $this;
        }

    }
