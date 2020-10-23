<?php

    namespace Wrapped\_\Database\Driver;

    class Postgres
    extends DatabaseDriver {

        const PDO_NAME = 'pgsql';

        public function quoteIdentifier( string $ident ): string {
            return sprintf( '"%s"', $ident );
        }

    }
