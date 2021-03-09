<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database;

    use \PDO;
    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\Driver\Mysql;
    use \verfriemelt\wrapped\_\Exception\Database\DatabaseDriverUnknown;
    use \verfriemelt\wrapped\_\Exception\Database\DatabaseException;

    class Database {

        private static $connections = [];

        public static function createNewConnection(
            $name, $driver, $username, $password, $host, $database, $port, $autoConnect = true ): DatabaseDriver {

            if ( !class_exists( $driver ) ) {
                throw new DatabaseDriverUnknown( "unknown driver {$driver}" );
            }

            self::$connections[$name] = new $driver( $name, $username, $password, $host, $database, $port );

            if ( $autoConnect ) {
                self::$connections[$name]->connect();
            }

            return self::$connections[$name];
        }

        /**
         *
         * @param type $name
         * @return Mysql
         * @throws DatabaseException
         */
        public static function getConnection( $name = "default" ): DatabaseDriver {
            if ( isset( self::$connections[$name] ) ) {
                return self::$connections[$name];
            }

            throw new DatabaseException( "No connection by that name, sorry" );
        }

    }
