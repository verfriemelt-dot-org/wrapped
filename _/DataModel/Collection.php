<?php

    namespace Wrapped\_\DataModel;

    use \ArrayAccess;
    use \Countable;
    use \Exception;
    use \Iterator;
    use \OutOfBoundsException;
    use \SeekableIterator;
    use \Wrapped\_\Database\Facade\QueryBuilder;

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

        public static function buildFromQuery( DataModel $prototype, QueryBuilder $query ) {
            return static::buildFromPdoResult( $prototype, $query->run() );
        }

        public static function buildFromPdoResult( DataModel $prototype, $result ) {

            $collection = new static();
            $instances  = [];

            while ( $data = $result->fetch() ) {
                $instances[] = (new $prototype() )->initData( $data );
            }

            return $collection->initialize( ... $instances );
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

        public function first() {

            if ( $this->count() == 0 ) {
                return null;
            }

            return $this->offsetGet( 0 );
        }

        /**
         * array access implementation
         * @return bool
         */
        public function offsetExists( $offset ): bool {

            if ( $offset < 0 || $offset >= $this->length ) {
                throw new Exception( "illegal offset {$offset} in result" );
            }

            // todo load more results
            if ( !isset( $this->data[$offset] ) ) {

                // data loading
                $obj = ($this->loadMoreCallback)( $offset );

                if ( !$obj ) {
                    throw new Exception( "unable to fetch offset {$offset}" );
                }

                $this->data[$offset] = $obj;
            }

            return isset( $this->data[$offset] );
        }

        /**
         * array access implementation
         * @return mixed
         */
        public function offsetGet( $offset ) {

            // validate offset
            $this->offsetExists( $offset );

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

            foreach ( $this as $element ) {
                $result[] = $callable( $element );
            }

            return $result;
        }

        public function filter( callable $function ): Collection {
            return new static( ... array_filter( $this->data, $function ) );
        }

        public function find( callable $function ): ?DataModel {

            foreach ( $this as $element ) {
                if ( $function( $element ) ) {
                    return $element;
                }
            }

            return null;
        }

        public function toArray() {
            return $this->data;
        }

    }
