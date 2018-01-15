<?php

    namespace Wrapped\_\DataModel\Collection;

    use \ArrayAccess;
    use \Countable;
    use \Exception;
    use \Iterator;
    use \OutOfBoundsException;
    use \PDO;
    use \PDOStatement;
    use \SeekableIterator;
    use \Wrapped\_\DataModel\DataModel;

    class CollectionResult
    implements Iterator, ArrayAccess, Countable, SeekableIterator {

        private $sqlResult      = null;
        private $objPrototype   = null;
        private $sqlResultAssoc = [];
        private $pointer        = 0;
        private $resultLength   = 0;
        private $resultObjects  = [];

        public function __construct( PDOStatement $sqlResult = null, DataModel $prototype = null ) {

            $this->sqlResult    = $sqlResult;
            $this->objPrototype = $prototype;

            if ( $sqlResult ) {
                $this->setResults( $this->sqlResult->fetchAll( PDO::FETCH_ASSOC ) );
            }
        }

        public function setResults( $results ): CollectionResult {
            $this->sqlResultAssoc = $results;
            $this->resultLength   = count( $this->sqlResultAssoc );
            return $this;
        }

        /**
         * countable implemententation
         * @return int
         */
        public function count(): int {
            return $this->resultLength;
        }

        public function isEmpty(): bool {
            return $this->count() === 0;
        }

        /**
         * iterator implementation
         * @return mixed
         */
        public function current() {
            return $this->offsetGet( $this->pointer );
        }

        /**
         * iterator implementation
         */
        public function key(): int {
            return $this->pointer;
        }

        /**
         * iterator implementation
         */
        public function next(): void {
            $this->pointer++;
        }

        /**
         * iterator implementation
         */
        public function rewind(): void {
            $this->pointer = 0;
        }

        /**
         * iterator implementation
         * @return bool
         */
        public function valid(): bool {
            return $this->pointer < $this->resultLength;
        }

        /**
         * returns the last element
         * @return mixed
         */
        public function last() {

            if ( $this->count() == 0 ) {
                return null;
            }

            return $this->offsetGet( $this->count() - 1 );
        }

        /**
         * array access implementation
         * @return bool
         */
        public function offsetExists( $offset ): bool {
            return isset( $this->sqlResultAssoc[$offset] );
        }

        /**
         * array access implementation
         * @return mixed
         */
        public function offsetGet( $offset ) {

            if ( $offset < 0 || $offset >= $this->resultLength ) {
                throw new Exception( "illegal offset {$offset} in result" );
            }

            if ( !isset( $this->resultObjects[$offset] ) ) {
                $this->resultObjects[$offset] = (new $this->objPrototype )->initData( $this->sqlResultAssoc[$offset] );
            }

            return $this->resultObjects[$offset];
        }

        /**
         * array access implementation
         * disabled
         */
        public function offsetSet( $offset, $value ) {
            throw new Exception( "not allowed to write to results" );
        }

        /**
         * array access implementation
         * disabled
         */
        public function offsetUnset( $offset ) {
            throw new Exception( "not allowed to write to results" );
        }

        public function fetchCollectionIds() {
            return $this->fetchColllectionValues( "id" );
        }

        public function seek( $position ) {

            $this->pointer = $position;

            if ( $position >= $this->resultLength ) {
                throw new OutOfBoundsException();
            }
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

            $values = [];

            foreach ( $this as $row ) {
                $values[] = $row->{$metod}();
            }

            return $values;
        }

        /**
         *
         * @param type $func
         * @param array $args
         * @deprecated since version 0
         * @throws Exception
         */
        public function propagateCall( $func, array $args = [] ) {
            return $this->map( $func, $args );
        }

        /**
         *
         * @param type $callable
         * @param array $args
         * @return array
         * @throws Exception
         */
        public function map( $callable, array $args = [] ): array {

            $results = [];

            if ( !is_callable( $callable ) && !is_callable( [ $this->objPrototype, $callable ] ) ) {
                throw new Exception( "illegal method {$callable} to propagate on object" );
            }

            foreach ( $this as $row ) {

                if ( is_callable( $callable ) ) {
                    $results[] = $callable( $row );
                } else {
                    $results[] = $row->{$callable}( ... $args );
                }
            }

            return $results;
        }

        public function sort( callable $callable ): CollectionResult {
            $this->initAllObjects();
            usort( $this->resultObjects, $callable );

            return $this;
        }

        private function initAllObjects() {
            for ( $i = 0; $i < $this->resultLength; $i++ ) {
                $this->offsetGet( $i );
            }
        }

        public function toArray() {
            $this->initAllObjects();
            return $this->resultObjects;
        }

    }
