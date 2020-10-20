<?php

    namespace Wrapped\_\DataModel;

    use \ArrayAccess;
    use \Countable;
    use \OutOfBoundsException;
    use \SeekableIterator;
    use \Iterator;
    use \Exception;

    class Collection
    implements Iterator, ArrayAccess, Countable, SeekableIterator {

        private int $length = 0;

        private int $pointer = 0;

        private array $data = [];

        private $loadMoreCallback;

        public function __construct( DataModel ... $data ) {

            if ( $data ) {
                $this->initialize( ... $data );
            }
        }

        public function setLoadingCallback( callable $func ) {
            $this->loadMoreCallback = $func;
            return $this;
        }

        public function setLength( int $lenght ) {
            $this->length = $lenght;
            return $this;
        }

        public function initialize( DataModel ... $data ) {

            $this->data   = $data;
            $this->length = count( $data );

            return $this;
        }

        /**
         * countable implemententation
         * @return int
         */
        public function count(): int {
            return $this->length;
        }

        public function isEmpty(): bool {
            return $this->count() === 0;
        }

        /**
         * iterator implementation
         * @return mixed
         */
        public function current() {
            return $this->valid() ? $this->offsetGet( $this->pointer ) : false;
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
            return $this->pointer < $this->length;
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

            if ( $offset > $length - 1 ) {
                return false;
            }

            // todo load more results

            return isset( $this->data[$offset] );
        }

        /**
         * array access implementation
         * @return mixed
         */
        public function offsetGet( $offset ) {

            if ( $offset < 0 || $offset >= $this->length ) {
                throw new Exception( "illegal offset {$offset} in result" );
            }

            if ( !isset( $this->data[$offset] ) ) {
                $this->data[$offset] = ($this->loadMoreCallback)( $offset );
            }

            return $this->data[$offset];
        }

        /**
         * array access implementation
         * disabled
         */
        public function offsetSet( $offset, $value ) {
            throw new Exception( "write only collections" );
        }

        /**
         * array access implementation
         * disabled
         */
        public function offsetUnset( $offset ) {
            throw new Exception( "write only collections" );
        }

        public function seek( $position ) {

            if ( $position >= $this->length ) {
                throw new OutOfBoundsException();
            }

            $this->pointer = $position;
        }

        /**
         *
         * @param type $callable
         * @param array $args
         * @return array
         * @throws Exception
         */
        public function map( callable $callable ): array {

            $result = [];

            foreach( $this as $element ) {
                $result[] = $callable( $element );
            }

            return $result;
        }

    }
