<?php

    namespace Wrapped\_\Database\Driver;

use \PDO;
use \PDOException;
use \PDOStatement;
use \Wrapped\_\Database\DbLogic;
use \Wrapped\_\Database\SQL\Command;
use \Wrapped\_\Database\SQL\Delete;
use \Wrapped\_\Database\SQL\Insert;
use \Wrapped\_\Database\SQL\Join;
use \Wrapped\_\Database\SQL\Select;
use \Wrapped\_\Database\SQL\Table;
use \Wrapped\_\Database\SQL\Update;
use \Wrapped\_\Exception\Database\DatabaseException;

    abstract class Driver {

        public $connectionName;
        protected $currentDatabase;
        protected $statements             = [];
        protected $lastStatement;
        protected $config                 = [];
        public static $debug              = true;
        public static $debugHistory       = [];
        public static $debugLastStatement = null;
        public static $debugLastParams    = null;
        public static $debugQuerieCount   = 0;
        public static $counter            = 0;
        public static $time               = 0;

        /** @var PDO */
        public $connectionHandle;

        abstract function quoteIdentifier( string $ident ): string;

        public function __construct( $name, $user, $password, $host, $database, $port = null ) {
            $this->connectionName = $name;

            $this->config["dbUsername"] = $user;
            $this->config["dbPassword"] = $password;
            $this->config["dbPassword"] = $password;
            $this->config["dbDatabase"] = $database;
            $this->config["dbHost"]     = $host;
            $this->config['dbPort']     = $port;
        }

        /**
         * returns PDO handle
         * @return PDO
         */
        public function fetchConnectionHandle(): \PDO {
            return $this->connectionHandle;
        }

        protected function getConnectionString() {

            $this->connectionString = static::PDO_NAME . ":host={$this->config["dbHost"]};";

            if ( $this->config['dbPort'] !== null ) {
                $this->connectionString .= "port={$this->config['dbPort']};";
            }

            $this->connectionString .= "dbname={$this->config["dbDatabase"]}";

            return $this->connectionString;
        }

        public function disconnet() {
            $this->connectionHandle = null;
        }

        public function connect() {

            try {
                $this->connectionHandle = new PDO(
                    $this->getConnectionString(), $this->config["dbUsername"], $this->config["dbPassword"]
                );

                // switch to error mode to exceptions
                $this->connectionHandle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            } catch ( PDOException $e ) {
                $msg = $e->getMessage();

                if ( $msg == "could not find driver" ) {
                    throw new DatabaseException( "PDO Mysql Driver not available" );
                }

                throw new DatabaseException( "PDO Exception {$e->getMessage()}" );
            }

            // unset config data
            $this->currentDatabase = $this->config["dbDatabase"];
            $this->config          = [];
        }

        /**
         * bind values to the pdo statement
         * @param type $statement
         * @param type $param
         * @param type $var
         * @return Mysql
         */
        public function bind( PDOStatement $statement, $param, $var ) {

            if ( is_array( $param ) && is_array( $var ) && \count( $param ) == \count( $var ) ) {
                for ( $i = 0, $count = \count( $var ); $i < $count; ++$i ) {
                    $statement->bindValue( ":" . $param[$i], $var[$i] );
                }
            } else {
                $statement->bindValue( ":" . $param, $var );
            }

            return $this;
        }

        protected function bindLast( $param, $var ) {

            self::$debugLastParams["param"][] = $param;
            self::$debugLastParams["var"][]   = $var;

            $this->bind( $this->lastStatement, $param, $var );
            return $this;
        }

        protected function freeLastStatement() {

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
        public function execute( PDOStatement $statement ) {

            $start = microtime( 1 );

            try {
                $this->lastresult = $statement->execute();
            } catch ( PDOException $e ) {
                throw new DatabaseException( $e->getMessage() . "\n\n" . self::$debugLastStatement . "\n\n" );
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

        public function select( string $table, $fetchMode = null ): Select {
            return (new Select( $this ) )->table( $table );
        }

        public function delete( string $table ): Delete {
            return (new Delete( $this ) )->table( $table );
        }

        public function update( string $table ): Update {
            return (new Update( $this ) )->table( $table );
        }

        public function insert( string $table ): Insert {
            return (new Insert( $this ) )->table( $table );
        }

        public function run( Command $command ) {

            $logic = $command->getDbLogic();
            $this->prepare( $command->compile() );

            if ( $logic ) {
                $bindings = $logic->getBindings();
                $this->bindLast( $bindings["params"], $bindings["vars"] );
            }

            foreach ( $command->fetchBindings() as $bind => $value ) {
                $this->bindLast( $bind, $value );
            }

            $this->executeLast();

            $result = $this->lastStatement;
            $result->setFetchMode( $command->getFetchMode() );

            $this->freeLastStatement();

            return $result;
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


        public function getCurrentDatabase() {
            return $this->currentDatabase;
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

        public function queryUnbound( $sql ) {
            $this->connectionHandle->query( $sql );
        }

        public function queryWithDbLogic( $sql, DbLogic $dbLogic ) {

            $this->prepare( $sql . $dbLogic->compile( $this ) );

            $bindings = $dbLogic->getBindings();
            $this->bindLast( $bindings["params"], $bindings["vars"] );

            $this->executeLast();

            $result = $this->lastStatement;
            $result->setFetchMode( PDO::FETCH_ASSOC );

            return $result;
        }

        public function join( $table ) {
            $t = new Table( $table );
            $j = new Join( $t, $this );

            return $t;
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

    }
