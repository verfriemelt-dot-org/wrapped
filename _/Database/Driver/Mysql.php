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

//        public function insert( $table, array $columnsData ) {
//
//            $statement = "INSERT INTO `{$table}` (`";
//
//            $columnNames  = array_keys( $columnsData );
//            $columnValues = array_values( $columnsData );
//
//            $statement .= implode( "`,`", $columnNames );
//            $statement .= "`) VALUES (:";
//
//            $statement .= implode( ",:", $columnNames );
//            $statement .= ")";
//
//            //prepare statement
//            $this->prepare( $statement );
//
//            //bind table to statement
//            //$this->bindLast("table", $table);
//            //bind values to statement
//            $this->bindLast( $columnNames, $columnValues );
//
//            $this->executeLast();
//            $this->freeLastStatement();
//
//            return $this->connectionHandle->lastInsertId();
//        }

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

            $this->prepare( $sql . $dbLogic->compile( $this ) );

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
