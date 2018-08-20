<?php

    namespace Wrapped\_\Database\Driver;

    class Postgres
    extends Driver {

        const PDO_NAME = 'pgsql';

        static $convertToLower = true;

        public static function castToLower( $bool = true ) {
            static::$convertToLower = $bool;
        }

        public function quoteIdentifier( string $ident ): string {
            if ( static::$convertToLower ) {
                return '"' . strtolower( $ident ) . '"';
            } else {
                return '"' . $ident . '"';
            }
        }

    }
