<?php

    declare( strict_types = 1 );

    namespace verfriemelt\wrapped\_\DateTime;

    use \verfriemelt\wrapped\_\DataModel\PropertyObjectInterface;

    final class DateTimeImmutable
    extends \DateTimeImmutable
    implements PropertyObjectInterface
    {

        const SQL_FORMAT = "Y-m-d H:i:s.u";

        public function toSqlFormat(): string
        {
            return $this->format( static::SQL_FORMAT );
        }

        public static function hydrateFromString( ?string $storedValue ): ?static
        {
            if ( $storedValue === null ) {
                return null;
            }
            return new static( $storedValue );
        }

        public function dehydrateToString(): string
        {
            return $this->toSqlFormat();
        }

        public function toString(): string
        {
            return $this->toSqlFormat();
        }

    }