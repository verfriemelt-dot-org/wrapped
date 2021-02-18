<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\Driver;

    use \PDO;
    use \PDOException;
    use \PDOStatement;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\SQL\Join;
    use \Wrapped\_\Database\SQL\QueryPart;
    use \Wrapped\_\Database\SQL\Table;
    use \Wrapped\_\Exception\Database\DatabaseException;

    abstract class DatabaseDriver {

        public $connectionName;

        protected $currentDatabase;

        protected $currentUsername;

        protected $statements = [];

        protected $lastStatement;

        protected $config = [];

        public static $debug = false;

        public static $debugHistory = [];

        public static $debugLastStatement = null;

        public static $debugLastParams = null;

        public static $debugQuerieCount = 0;

        public static $counter = 0;

        public static $time = 0;

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
            $this->currentUsername = $this->config["dbUsername"];
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

            $type = PDO::PARAM_STR;

            switch ( gettype( $var ) ) {
                case 'boolean':
                    $type = PDO::PARAM_BOOL;
                    break;
                case "integer":
                    $type = PDO::PARAM_INT;
                    break;
                case 'NULL':
                    $type = PDO::PARAM_NULL;
                    break;
            }

            $statement->bindValue( $param, $var, $type );

            return $this;
        }

        protected function bindLast( $param, $var ) {

            if ( self::$debug ) {
                self::$debugLastParams["param"][] = $param;
                self::$debugLastParams["var"][]   = $var;
            }

            $this->bind( $this->lastStatement, $param, $var );
            return $this;
        }

        /**
         *
         * @param type $statement
         * @return boolean
         * @throws Exception
         */
        public function execute( PDOStatement $statement ) {

            $start = microtime( true );

            try {
                $this->lastresult = $statement->execute();
            } catch ( PDOException $e ) {
                throw new DatabaseException( $e->getMessage() . "\n\n" . self::$debugLastStatement . "\n\n" );
            }

            $time = microtime( true ) - $start;

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
        public function prepare( $statement ) {

            if ( self::$debug ) {
                self::$debugLastParams    = [];
                self::$debugLastStatement = $statement;
            }

            $this->lastStatement = $this->connectionHandle->prepare( $statement );

            return $this;
        }

        public function run( QueryPart $query ) {

            $this->prepare( $query->stringify( $this ) );

            foreach ( $query->fetchBindings() as $bind => $value ) {
                $this->bindLast( $bind, $value );
            }

            $this->executeLast();

            $result = $this->lastStatement;
            $result->setFetchMode( PDO::FETCH_ASSOC );

            return $result;
        }

        public function quote( $data ) {
            return $this->connectionHandle->quote( $data );
        }

        public function truncate( $tableName ) {

            $statement = "TRUNCATE {$tableName} RESTART IDENTITY CASCADE";
            $this->prepare( $statement );
            $this->executeLast();

            $result = $this->lastStatement->rowCount();

            return $result;
        }

        public function getCurrentDatabase() {
            return $this->currentDatabase;
        }

        public function getCurrentUsername() {
            return $this->currentUsername;
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
        public function query( $sql ) {
            $this->prepare( $sql );
            $this->executeLast();
            return $this->lastStatement;
        }

        public function queryUnbound( $sql ) {
            $this->connectionHandle->query( $sql );
        }

        /**
         * executes raw querie
         * @param type $sql
         * @return PDOStatement
         */
        public function queryWithDbLogic( $sql, DbLogic $dbLogic, $precompiled = false ) {

            // uh is this hacky
            if ( !$precompiled ) {
                $this->prepare( $sql . $dbLogic->compile( $this ) );
            } else {
                $this->prepare( $sql );
            }

            foreach ( $dbLogic->fetchBindings() as $bind => $value ) {
                $this->bindLast( $bind, $value );
            }

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

        public function setAttribute( int $key, $value ) {
            $this->connectionHandle->setAttribute( $key, $value );
            return $this;
        }

    }
