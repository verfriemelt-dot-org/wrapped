<?php

    namespace Wrapped\_\Database\Driver;

    class Postgres
    extends Driver {

        const PDO_NAME = 'pgsql';

        public function quoteIdentifier( string $ident ): string {
            return "\"{$ident}\"";
        }

    }
