<?php

    namespace Wrapped\_\Database\Driver;

    use \Exception;
    use \PDO;
    use \PDOException;
    use \PDOStatement;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\SQL\Join;
    use \Wrapped\_\Database\SQL\Table;

    class Postgres
    extends Database {

        public $config                    = [];
        public static $debug              = false;
        public static $debugHistory       = [];
        public static $debugLastStatement = null;
        public static $debugLastParams    = null;
        public static $debugQuerieCount   = 0;
        public static $counter            = 0;
        public static $time               = 0;
        public $connectionName;

        /** @var PDO */
        public $connectionHandle;
        private $currentDatabase;
        private $statements = [];
        private $lastStatement;

        public function __construct( $name, $user, $password, $host, $database, $port = null ) {
            $this->connectionName = $name;

            $this->config["dbUsername"] = $user;
            $this->config["dbPassword"] = $password;
            $this->config["dbPassword"] = $password;
            $this->config["dbDatabase"] = $database;
            $this->config["dbHost"]     = $host;
            $this->config['dbPort'] = $port;
        }

        public function connect() {

            $this->connectionHandle = new PDO(
                $this->getConnectionString(), $this->config["dbUsername"], $this->config["dbPassword"]
            );

            // switch to error mode to exceptions
            $this->connectionHandle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            // unset config data
            $this->currentDatabase = $this->config["dbDatabase"];
            $this->config          = [];
        }

        private function getConnectionString() {
            $this->connectionString = "pgsql:host={$this->config["dbHost"]};";

            if ( $this->config['dbPort'] !== null ) {
                $this->connectionString .= ";port={$this->config['dbPort']}";
            }

            $this->connectionString .= ";dbname={$this->config["dbDatabase"]}";

            return $this->connectionString;
        }

        public function enableUnbufferedMode( $bool = true ) {
            $this->connectionHandle->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, !$bool );
            return $this;
        }

        /**
         * bind values to the pdo statement
         * @param type $statement
         * @param type $param
         * @param type $var
         * @return Mysql
         */
        public function bind( $statement, $param, $var ) {

            if ( is_array( $param ) && is_array( $var ) && \count( $param ) == \count( $var ) ) {
                for ( $i = 0, $count = \count( $var ); $i < $count; ++$i ) {
                    $statement->bindValue( ":" . $param[$i], $var[$i] );
                }
            } else {
                $statement->bindValue( ":" . $param, $var );
            }

            return $this;
        }

        private function bindLast( $param, $var ) {

            self::$debugLastParams["param"][] = $param;
            self::$debugLastParams["var"][]   = $var;

            $this->bind( $this->lastStatement, $param, $var );
            return $this;
        }

        private function freeLastStatement() {

            if ( ($key = array_search( $this->lastStatement, $this->statements )) !== false ) {
                unset( $this->statements[$key] );
            }
        }

        /**
         *
         * @param type $statement
         * @return boolean
         * @throws Exception
         */
        public function execute( $statement ) {

            $start = microtime( 1 );

            $this->connectionHandle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            try {
                $this->lastresult = $statement->execute();
            } catch ( PDOException $e ) {
                throw new Exception( $e->getMessage() . "\n\n" . self::$debugLastStatement . "\n\n" );
            }

            $time = microtime( 1 ) - $start;

            if ( static::$debug ) {

//                $trace = debug_backtrace();
//                $log = [];
//
//                foreach( $trace as $entry ) {
//
//                    if ( !isset($entry["file"])) {
//                        continue;
//                    }
//
//                    $log[] = $entry["file"] . ":" . $entry["line"];
//                }

                static::$debugHistory[] = [
                    "con"       => $this->connectionName,
                    "count"     => ++static::$debugQuerieCount,
                    "time"      => $time, "statement" => self::$debugLastStatement,
                    "data"      => self::$debugLastParams,
//                    "stack" => $log
                ];
            }

            static::$time += $time;
            return true;
        }

        /**
         *
         * @return PDOStatement
         */
        public function getLastResult() {
            return $this->lastStatement;
        }

        /**
         * executes last prepared statement
         * @return bool
         */
        public function executeLast() {
            return $this->execute( $this->lastStatement );
        }

        /**
         * prepares statement
         * @param type $statement
         * @return Mysql
         */
        public function prepare( $statement, $prepareOptions = [] ) {

            if ( self::$debug ) {
                self::$debugLastParams    = [];
                self::$debugLastStatement = $statement;
            }

            $name = md5( $statement );

            if ( array_key_exists( $name, $this->statements ) ) {

                $this->lastStatement = $this->statements[$name];
                return $this;
            }

            $this->statements[$name] = $this->connectionHandle->prepare( $statement );
            $this->lastStatement     = $this->statements[$name];

            return $this;
        }

        public function insert( $table, array $columnsData ) {

            $statement = "INSERT INTO `{$table}` (`";

            $columnNames  = array_keys( $columnsData );
            $columnValues = array_values( $columnsData );

            $statement .= implode( "`,`", $columnNames );
            $statement .= "`) VALUES (:";

            $statement .= implode( ",:", $columnNames );
            $statement .= ")";

            //prepare statement
            $this->prepare( $statement );

            //bind table to statement
            //$this->bindLast("table", $table);
            //bind values to statement
            $this->bindLast( $columnNames, $columnValues );

            $this->executeLast();
            $this->freeLastStatement();

            return $this->connectionHandle->lastInsertId();
        }

        /**
         * updates db record
         * @param type $table
         * @param array $columnsData
         * @param DbLogic $dbLogic
         * @return PDOStatement
         */
        public function update( $table, array $columnsData, $dbLogic = null ) {

            $statement = "UPDATE `{$table}` SET ";

            $columnNames = array_keys( $columnsData );
            $columnCount = \count( $columnNames );

            for ( $i = 0; $i < $columnCount; ++$i ) {
                $statement .= "`{$columnNames[$i]}` = :{$columnNames[$i]}";

                if ( $i != $columnCount - 1 )
                    $statement .= ",";
            }

            if ( $dbLogic !== null ) {
                $statement .= $dbLogic->getString();
            }

            $this->prepare( $statement );

            //bind update vars
            for ( $i = 0; $i < $columnCount; ++$i ) {
                $this->bindLast( $columnNames[$i], $columnsData[$columnNames[$i]] );
            }

            //bind where statement
            if ( $dbLogic !== null ) {
                $bindings = $dbLogic->getBindings();
                $this->bindLast( $bindings["params"], $bindings["vars"] );
            }

            $this->executeLast();
            $result = $this->lastStatement->rowCount();
            $this->freeLastStatement();

            return $result;
        }

        /**
         * delete entries from tables
         * @param type $table
         * @param array $params
         */
        public function delete( $table, DbLogic $where ) {

            $statement = "DELETE FROM `{$table}` {$where->getString()}";
            $this->prepare( $statement );

            //bind where statement
            if ( $where !== null ) {
                $bindings = $where->getBindings();
                $this->bindLast( $bindings["params"], $bindings["vars"] );
            }

            $this->executeLast();
            $result = $this->lastStatement->rowCount();
            $this->freeLastStatement();

            return $result;
        }

        /**
         *
         * @param type $table
         * @param type $what
         * @param DbLogic $dbLogic
         * @param type $fetchMode
         * @return PDOStatement
         */
        public function select( $table, $what = "*", $dbLogic = null, $fetchMode = null ) {

            $statement = "SELECT {$what} FROM `{$table}` ";

            if ( $dbLogic !== null ) {
                $statement .= $dbLogic->getString();
            }

            $this->prepare( $statement );

            //bind where statement
            if ( $dbLogic !== null ) {
                $bindings = $dbLogic->getBindings();
                $this->bindLast( $bindings["params"], $bindings["vars"] );
            }

            $this->executeLast();

            $result = $this->lastStatement;
            $result->setFetchMode( $fetchMode ?: PDO::FETCH_ASSOC  );

            $this->freeLastStatement();

            return $result;
        }

        /**
         * executes raw querie
         * @param type $sql
         * @return PDOStatement
         */
        public function query( $sql, $prepareOptions = [] ) {
            $this->prepare( $sql, $prepareOptions );
            $this->executeLast();
            return $this->lastStatement;
        }

        public function queryWithDbLogic( $sql, DbLogic $dbLogic ) {

            $this->prepare( $sql . $dbLogic->getString() );

            $bindings = $dbLogic->getBindings();
            $this->bindLast( $bindings["params"], $bindings["vars"] );

            $this->executeLast();

            $result = $this->lastStatement;
            $result->setFetchMode( PDO::FETCH_ASSOC );

            return $result;
        }

        public function executeJoin( Join $join ) {

            $join->prepare();
            $sql = $join->getStatement();

            $this->prepare( $sql );

            $bindings = $join->getDbLogic()->getBindings();
            $this->bindLast( $bindings["params"], $bindings["vars"] );

            $this->executeLast();

            $stmt = $this->lastStatement;
            $stmt->setFetchMode( PDO::FETCH_ASSOC );

            return $stmt;
        }

        public function join( $table ) {
            $t = new Table( $table );
            $j = new Join( $t, $this );

            return $t;
        }

        public function getCurrentDatabase() {
            return $this->currentDatabase;
        }

        public function quote( $string ) {
            return $this->connectionHandle->quote( $string );
        }

        public function truncate( $tableName ) {

            $statement = "TRUNCATE {$tableName}";
            $this->prepare( $statement );
            $this->executeLast();

            $result = $this->lastStatement->rowCount();
            $this->freeLastStatement();

            return $result;
        }

        /**
         *
         * @return bool
         */
        public function startTransaction() {
            return $this->connectionHandle->beginTransaction();
        }

        /**
         *
         * @return bool
         */
        public function inTransaction() {
            return $this->connectionHandle->inTransaction();
        }

        /**
         *
         * @return bool
         */
        public function rollbackTransaction() {
            return $this->connectionHandle->rollBack();
        }

        /**
         *
         * @return bool
         */
        public function commitTransaction() {
            return $this->connectionHandle->commit();
        }

        public function fetchTableNames() {

            $tableNames = [];

            foreach ( $this->query( "SHOW TABLES" )->fetchAll() as $tableName ) {
                $tableNames[] = $tableName[0];
            }

            return $tableNames;
        }

    }
