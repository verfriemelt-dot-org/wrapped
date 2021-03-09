<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel\Type;

    use \verfriemelt\wrapped\_\DataModel\PropertyObjectInterface;

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
            return $storedValue === null ? null : new self( $storedValue );
        }

        public function dehydrateToString(): string {
            return $this->toSqlFormat();
        }

        public function toString(): string {
            return $this->toSqlFormat();
        }

    }
