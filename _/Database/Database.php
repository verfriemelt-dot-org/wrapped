<?php

    namespace Wrapped\_\Database;

    use \PDO;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\Driver\Mysql;
    use \Wrapped\_\Exception\Database\DatabaseDriverUnknown;
    use \Wrapped\_\Exception\Database\DatabaseException;

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
