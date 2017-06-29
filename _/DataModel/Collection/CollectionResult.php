<?php

    namespace Wrapped\_\DataModel\Collection;

    class CollectionResult
    implements \Iterator, \ArrayAccess, \Countable {

        private $sqlResult    = null;
        private $objPrototype = null;
        private $results      = [];
        private $_cache       = [];

        public function __construct( $sqlResult = null, $prototype = null ) {
            $this->sqlResult    = $sqlResult;
            $this->objPrototype = $prototype;

            // fetch sql results

            if ( $sqlResult ) {
                $this->results = $this->sqlResult->fetchAll( \PDO::FETCH_ASSOC );
            }
        }

        public function setResults( $results ) {
            $this->results = $results;
            return $this;
        }

        /**
         * countable implemententation
         * @return type
         */
        public function count() {
            return count( $this->results );
        }

        public function isEmpty() {
            return $this->count() === 0;
        }

        /**
         * iterator implementation
         * @return mixed
         */
        public function current() {
            return $this->offsetGet( $this->key() );
        }

        /**
         * iterator implementation
         * @return mixed
         */
        public function key() {
            return key( $this->results );
        }

        /**
         * iterator implementation
         */
        public function next() {
            next( $this->results );
        }

        /**
         * iterator implementation
         */
        public function rewind() {
            reset( $this->results );
        }

        /**
         * iterator implementation
         * @return bool
         */
        public function valid() {
            return isset( $this->results[$this->key()] );
        }

        public function last() {

            if ( $this->count() == 0 ) {
                return null;
            }

            return $this->offsetGet( $this->count() - 1);
        }

        /**
         * array access implementation
         * @return bool
         */
        public function offsetExists( $offset ) {
            return isset( $this->results[$offset] );
        }

        /**
         * array access implementation
         * @return mixed
         */
        public function offsetGet( $offset ) {

            if ( !isset( $this->_cache[$offset] ) ) {

                if ( !isset( $this->results[$offset] ) ) {
                    throw new \Exception( "illegal offset {$offset} in result" );
                }

                $this->_cache[$offset] = (new $this->objPrototype )->initData( $this->results[$offset] );
            }

            return $this->_cache[$offset];
        }

        /**
         * array access implementation
         * disabled
         */
        public function offsetSet( $offset, $value ) {
            throw new \Exception( "not allowed to write to results" );
        }

        /**
         * array access implementation
         * disabled
         */
        public function offsetUnset( $offset ) {
            throw new \Exception( "not allowed to write to results" );
        }

        public function fetchCollectionIds() {
            return $this->fetchColllectionValues( "id" );
        }

        /**
         *
         * @param type $key
         * @return mixed
         */
        public function fetchColllectionValues( $key ) {

            $metod = "get" . ucfirst( $key );
            if ( !is_callable( [ $this->objPrototype, $metod ] ) ) {
                return [];
            }

            $this->rewind();
            $values = [];

            foreach ( $this as $row ) {
                $values[] = $row->{$metod}();
            }

            return $values;
        }

        /**
         *
         * @param type $name
         * @param array $args
         * @return boolean
         * @throws \Exception
         */
        public function propagateCall( $name, array $args = [] ) {

            $results = [];

            if ( !is_callable( [ $this->objPrototype, $name ] ) ) {
                throw new \Exception( "illegal method {$name} to propagate on object" );
            }

            $this->rewind();
            foreach ( $this as $row ) {
                $results[] = $row->{$name}();
            }

            return $results;
        }

        public function toArray() {
            $tmp = [];

            foreach ( $this as $item ) {
                $tmp[] = $item;
            }

            return $tmp;
        }

    }
