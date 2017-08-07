<?php

    namespace Wrapped\_\DataModel\Collection;

    use \ArrayAccess;
    use \Countable;
    use \Exception;
    use \Iterator;
    use \PDO;
    use \PDOStatement;
    use \Wrapped\_\DataModel\DataModel;

    class CollectionResultYield
    implements Iterator, Countable, ArrayAccess {

        private $sqlResult    = null;
        private $objPrototype = null;
        private $lastInstance = null;
        private $index        = 0;

        public function __construct( PDOStatement $sqlResult = null, DataModel $prototype = null ) {

            $this->sqlResult    = $sqlResult;
            $this->objPrototype = $prototype;

            if ( $this->count() > 0 ) {
                $this->lastInstance = (new $this->objPrototype )->initData( $this->sqlResult->fetch( PDO::FETCH_ASSOC ) );
            }
        }

        private function readNext() {

            $this->index++;
            $data = $this->sqlResult->fetch( PDO::FETCH_ASSOC );

            if ( $data !== false ) {
                $this->lastInstance = (new $this->objPrototype )->initData( $data );
            } else {
                $this->lastInstance = null;
            }
        }

        /**
         * countable implemententation
         * @return type
         */
        public function count() {
            return $this->sqlResult->rowCount();
        }

        public function isEmpty() {
            return $this->count() === 0;
        }

        /**
         * iterator implementation
         * @return mixed
         */
        public function current() {
            return $this->lastInstance;
        }

        /**
         * iterator implementation
         * @return mixed
         */
        public function key() {
            return $this->index;
        }

        /**
         * iterator implementation
         */
        public function next() {
            $this->readNext();
        }

        /**
         * iterator implementation
         */
        public function rewind() {
            // nope
        }

        /**
         * iterator implementation
         * @return bool
         */
        public function valid() {
            return $this->index < $this->count();
        }

        /**
         * array access implementation
         * @return bool
         */
        public function offsetExists( $offset ) {
            return $offset <= $this->count();
        }

        /**
         * array access implementation
         * @return mixed
         */
        public function offsetGet( $offset ) {

            if ( $offset < $this->index ) {
                throw new Exception( "cannot scroll back in Collection Results" );
            }

            // fast forward
            while ( $offset > $this->index ) {
                $this->readNext();
            }

            return $this->lastInstance;
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
            return $this->propagateCall( "getId" );
        }

        /**
         *
         * @param type $name
         * @param array $args
         * @return boolean
         * @throws Exception
         */
        public function propagateCall( string $name, array $args = [] ) {

            $results = [];

            if ( !is_callable( [ $this->objPrototype, $name ] ) ) {
                throw new Exception( "illegal method {$name} to propagate on object" );
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
