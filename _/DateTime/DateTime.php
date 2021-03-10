<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DateTime;

    use \verfriemelt\wrapped\_\DataModel\PropertyObjectInterface;

    class DateTime
    extends \DateTime
    implements PropertyObjectInterface {

        const SQL_FORMAT = "Y-m-d H:i:s.u";

        public function toSqlFormat(): string {
            return $this->format( static::SQL_FORMAT );
        }

        public static function hydrateFromString( $storedValue ) {
            /** @phpstan-ignore-next-line */
            return $storedValue === null ? null : new static( $storedValue );
        }

        public function dehydrateToString(): string {
            return $this->toSqlFormat();
        }

        public function toString(): string {
            return $this->toSqlFormat();
        }

    }
