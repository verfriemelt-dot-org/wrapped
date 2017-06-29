<?php namespace Wrapped\_\Database;

    class Database {

        private static $connections = [ ];

        /**
         *
         * @param type $name
         * @param type $type
         * @param type $autoConnect
         * @return Database\Driver\Mysql
         */
        public static function createNewConnection(
          $name, $driver, $username, $password, $host, $database, $autoConnect = true ) {

            if ( !class_exists( $driver ) ) {
                throw new Exception\Database\DatabaseDriverUnknown( "unknown driver {$driver}" );
            }

            self::$connections[$name] = new $driver( $name, $username, $password, $host, $database );

            if ( $autoConnect ) {
                self::$connections[$name]->connect();
            }

            return self::$connections[$name];
        }

        /**
         *
         * @param type $name
         * @return Driver\Mysql
         * @throws \Exception
         */
        public static function getConnection( $name = "default" ) {
            if ( isset( self::$connections[$name] ) ) {
                return self::$connections[$name];
            }

            throw new \Exception( "No connection by that name, sorry" );
        }

        /**
         * get the connection state
         * @return type
         */
        public function isConnected() {
            return $this->connected;
        }

        /**
         * returns PDO handle
         * @return \PDO
         */
        public function fetchConnectionHandle() {
            return $this->connectionHandle;
        }
    }
