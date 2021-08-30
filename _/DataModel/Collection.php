<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel;

    use \ArrayAccess;
    use \Countable;
    use \Exception;
    use \Iterator;
    use \JsonSerializable;
    use \OutOfBoundsException;
    use \SeekableIterator;
    use \verfriemelt\wrapped\_\Database\Facade\QueryBuilder;

    class Collection
    implements Iterator, ArrayAccess, Countable, SeekableIterator, JsonSerializable {

        private int $length = 0;

        private int $pointer = 0;

        private array $data = [];

        private $loadMoreCallback;

        final public function __construct( DataModel ... $data ) {
            $this->initialize( ... $data );
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

        public function current():mixed {
            return $this->valid() ? $this->offsetGet( $this->pointer ) : false;
        }

        public function key(): int {
            return $this->pointer;
        }

        public function next(): void {
            $this->pointer++;
        }

        public function rewind(): void {
            $this->pointer = 0;
        }

        public function valid(): bool {
            return $this->pointer < $this->length;
        }

        public function last():mixed {

            if ( $this->count() == 0 ) {
                return null;
            }

            return $this->offsetGet( $this->count() - 1 );
        }

        public function first():mixed {

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

        public function offsetGet( mixed $offset ):mixed {

            // validate offset
            $this->offsetExists( $offset );

            return $this->data[$offset];
        }

        /**
         * array access implementation
         * disabled
         */
        public function offsetSet( mixed $offset, mixed $value ): void {
            throw new Exception( "write only collections" );
        }

        /**
         * array access implementation
         * disabled
         */
        public function offsetUnset( mixed $offset ): void {
            throw new Exception( "write only collections" );
        }

        public function seek( int $position ): void {

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

        public function call( callable $function ) {
            array_map( $function, $this->data );
            return $this;
        }

        public function filter( callable $function ): Collection {
            return new static( ... array_filter( $this->data, $function ) );
        }

        public function reverse(): Collection {
            return new static( ... array_reverse( $this->data ) );
        }

        public function reduce( callable $function, $initial = null ) {
            return array_reduce( $this->data, $function, $initial );
        }

        public function sort( callable $function ) {

            $copy = $this->data;
            usort( $copy, $function );

            return new static( ... $copy );
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

        public function jsonSerialize():mixed  {
            return $this->toArray();
        }

    }
