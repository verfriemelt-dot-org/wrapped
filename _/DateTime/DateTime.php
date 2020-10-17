<?php

    namespace Wrapped\_\DateTime;

    use \Wrapped\_\DataModel\PropertyObjectInterface;

    class DateTime
    extends \DateTimeImmutable
    implements PropertyObjectInterface {

        const SQL_FORMAT = "Y-m-d H:i:s";

        public function toSqlFormat(): string {
            return $this->format( static::SQL_FORMAT );
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
