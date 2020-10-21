<?php

    namespace Wrapped\_\Database\Driver;

    use \PDO;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\Driver\Mysql\Schema;

    class Mysql
    extends DatabaseDriver {

        const PDO_NAME = 'mysql';

        public function quoteIdentifier( string $ident ): string {
            return "`{$ident}`";
        }

        public function enableUnbufferedMode( $bool = true ) {
            $this->connectionHandle->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, !$bool );
            return $this;
        }

        /**
         *
         * @param type $name
         * @return Schema
         */
        public function createSchema( $name ) {
            return Schema::create( $name, $this );
        }

        /**
         *
         * @param type $name
         * @return Schema
         */
        public function getSchema( $name ) {
            return $this->createSchema( $name );
        }

    }
