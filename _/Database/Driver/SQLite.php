<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\Driver;

    class SQLite
    extends DatabaseDriver {

        const PDO_NAME = 'sqlite::memory:';

        private string $databaseVersion;

        protected function getConnectionString(): string {
            return self::PDO_NAME;
        }

        public function quoteIdentifier( string $ident ): string {
            return sprintf( '"%s"', $ident );
        }

        public function connect(): void {
            parent::connect();

            $result = $this->query('SELECT sqlite_version()')->fetch();

            if ( !is_array($result) ) {
                throw new \RuntimeException('cannot fetch version');
            }

            $this->databaseVersion = $result["sqlite_version"];
        }

        public function getVersion(): float {

            $parts = explode(".", $this->databaseVersion);

            return (float) "{$parts[0]}.{$parts[1]}";
        }

    }
