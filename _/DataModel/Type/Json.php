<?php

    namespace Wrapped\_\DataModel\Type;

    use \Wrapped\_\DataModel\PropertyObjectInterface;

    class Json
    implements PropertyObjectInterface {

        public $data;

        public function __construct( string $data ) {
            $this->data = json_decode( $data );
        }

        public function toSqlFormat(): string {
            return json_encode( $this->data );
        }

        public static function hydrateFromString( $storedValue ) {
            return $storedValue === null ? null : new static( $storedValue );
        }

        public function dehydrateToString(): string {
            return $this->toSqlFormat();
        }

        public function toString(): string {
            return $this->toSqlFormat();
        }

    }
