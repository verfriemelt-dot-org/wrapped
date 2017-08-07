<?php

    namespace Wrapped\_\DataModel\Collection;

    use \Countable;
    use \Exception;
    use \Iterator;
    use \PDO;
    use \PDOStatement;
    use \Wrapped\_\DataModel\DataModel;

    class CollectionResultYield
    implements Iterator, Countable {

        private $sqlResult    = null;
        private $objPrototype = null;
        private $lastInstance = null;
        private $index        = 0;

        public function __construct( PDOStatement $sqlResult = null, DataModel $prototype = null ) {

            $this->sqlResult    = $sqlResult;
            $this->objPrototype = $prototype;

            $this->readNext();
            $this->index = 0;
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
         * returns 0 with unbuffered queries
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
            return $this->lastInstance !== null;
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
