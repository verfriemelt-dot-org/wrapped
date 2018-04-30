<?php

    namespace Wrapped\_\Database\Driver;

use \PDO;
use \PDOStatement;
use \Wrapped\_\Database\DbLogic;
use \Wrapped\_\Database\Driver\Mysql\Schema;
use \Wrapped\_\Database\SQL\Join;
use \Wrapped\_\Database\SQL\Table;

    class Mysql
    extends Driver {

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
