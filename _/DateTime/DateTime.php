<?php

    namespace Wrapped\_\DateTime;

    use \Wrapped\_\DataModel\PropertyObjectInterface;

    class DateTime
    extends \DateTime
    implements PropertyObjectInterface {

        const SQL_FORMAT = "Y-m-d H:i:s";

        public function toSqlFormat(): string {
            return $this->format( self::SQL_FORMAT );
        }

        public static function hydrateFromString( $storedValue ) {
            return new static( $storedValue );
        }

        public function dehydrateToString(): string {
            return $this->toSqlFormat();
        }

    }
