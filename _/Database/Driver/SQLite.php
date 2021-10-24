<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\Driver;

    class SQLite
    extends DatabaseDriver {

        const PDO_NAME = 'sqlite::memory:';

        protected function getConnectionString(): string {
            return self::PDO_NAME;
        }

        public function quoteIdentifier( string $ident ): string {
            return sprintf( '"%s"', $ident );
        }

    }
