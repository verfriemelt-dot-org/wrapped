<?php

    namespace Wrapped\_\Database\Driver\Mysql;

    use \Exception;
    use \PDO;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Mysql;

    class Schema {

        protected $tableName;

        /** @var Mysql */
        protected $mainDatabase;

        /** @var Mysql */
        protected $informationDatabase;
        private $dropTableFlag = false;
        private $autoincrement;
        private $columns       = [];
        private $indecies      = [];

        public function __construct( $name, Database $mainDatabase = null, Database $informationSchema = null ) {
            $this->tableName         = $name;
            $this->mainDatabase      = $mainDatabase ?: Database::getConnection();
            $this->informationSchema = $informationSchema ?: Database::getConnection( "information-schema" );
        }

        /**
         * sets table name
         * @param string $name
         * @return $this
         */
        public function setTableName( $name ) {
            $this->tableName = $name;
            return $this;
        }

        /**
         * returns schema table name
         * @return string
         */
        public function getTableName() {
            return $this->tableName;
        }

        /**
         *
         * @param type $tableName
         * @return static
         */
        public static function create( $tableName, Database $connection = null ) {
            return new static( $tableName, $connection );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function increments( $name ) {

            $col = $this->addColumn( (new SchemaColumn( $name ) )->increment()->type( "int" )->length( 11 ) );
            $this->primaryIndex( $name );

            $this->autoincrement = $col;

            return $col;
        }

        public function timestamp( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "timestamp" ) );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function polygon( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "Polygon" ) );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function point( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "Point" ) );
        }

        /**
         *
         * @param type $name
         * @return type
         */
        public function varchar( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "varchar" )->length( 255 ) );
        }

        /**
         *
         * @param type $name
         * @return type
         */
        public function decimal( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "decimal" ) );
        }

        /**
         *
         * @param type $name
         * @return type
         */
        public function float( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "float" ) );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function text( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "text" ) );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function enum( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "enum" ) );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function longtext( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "text" ) );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function datetime( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "datetime" ) );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function int( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "int" ) );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function tinyint( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "tinyint" ) );
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function smallint( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->type( "smallint" ) );
        }

        /**
         *
         * @param SchemaColumn $column
         * @return SchemaColumn
         */
        private function addColumn( SchemaColumn $column ) {
            return $this->columns[$column->getName()] = $column;
        }

        /**
         *
         * @param type $name
         * @return SchemaColumn
         */
        public function drop( $name ) {
            return $this->addColumn( (new SchemaColumn( $name ) )->drop() );
        }

        /**
         * check if the schema exists exists
         * @return type
         */
        public function exists() {

            $count = InformationSchema\Columns::count( "*", [
                    "TABLE_SCHEMA" => $this->mainDatabase->getCurrentDatabase(),
                    "TABLE_NAME"   => $this->tableName
                ] );

            return $count > 0;
        }

        /**
         * check if column exists within table
         * @param type $column
         * @return bool
         */
        public function hasColumn( $column ) {

            $count = InformationSchema\Columns::count( "*", [
                    "TABLE_SCHEMA" => $this->mainDatabase->getCurrentDatabase(),
                    "TABLE_NAME"   => $this->tableName,
                    "COLUMN_NAME"  => $column
                ] );

            return $count > 0;
        }

        private function createTable() {

            $strings = [];
            $syntax  = " CREATE TABLE IF NOT EXISTS `{$this->tableName}` ( ";

            foreach ( $this->columns as $column ) {

                if ( $column->dropped() ) {

                    // dont create columns which are dropped
                    continue;
                }

                $strings[] = $column->stringify();
            }

            $syntax .= implode( ",\n", $strings );
            $syntax .= " ) ";

            $this->mainDatabase->query( $syntax );
        }

        private function updateTable() {

            $strings = [];
            $syntax  = "ALTER TABLE `{$this->tableName}` ";

            foreach ( $this->columns as $column ) {

                if ( $column->dropped() ) {
                    $strings[] = " DROP `{$column->getName()}` ";
                } else {
                    $strings[] = ($this->hasColumn( $column->getName() ) ? " CHANGE `{$column->getOldName()}`  " : " ADD ") . " {$column->stringify()}";
                }
            }

            $syntax .= implode( ",\n", $strings ) . ";";

            $this->mainDatabase->query( $syntax );
        }

        private function deleteTable() {
            $syntax = "DROP TABLE `{$this->tableName}`";
            $this->mainDatabase->query( $syntax );
        }

        public function dropTable( $boolean = true ) {
            $this->dropTableFlag = $boolean;
            return $this;
        }

        /**
         * saves schema definition to database;
         * @return Schema
         */
        public function save() {

            if ( $this->exists() && $this->dropTableFlag ) {

                $this->deleteTable();

                return true;
            } elseif ( !$this->exists() && $this->dropTableFlag ) {
                throw new Exception( "Cant drop an nonexisting Table" );
            }

            $this->exists() ? $this->updateTable() : $this->createTable();


            foreach( $this->indecies as $index ) {
                $this->mainDatabase->query( $index->stringify() );
            }

            if ( $this->autoincrement ) {
                $syntax  = "ALTER TABLE `{$this->tableName}` CHANGE `{$this->autoincrement->getName()}` ";
                $syntax  .= $this->autoincrement->stringifyWithIncrement();
            }

            return $this;
        }

        /**
         * adds index to schema
         * @return static
         */
        public function index( $name, ... $columns ) {

            $index                 = new SchemaIndex( $name, $this );
            $this->indecies[$name] = $index;

            foreach ( $columns as $column ) {
                $index->addColumn( new SchemaColumn( $column ) );
            }

            return $this;
        }

        /**
         * adds unique index to schema
         * @return static
         */
        public function uniqueIndex( $name, ... $columns ) {
            $this->index( $name, ... $columns );
            $this->indecies[$name]->unique();

            return $this;
        }

        /**
         * adds index to schema
         * @return static
         */
        public function primaryIndex( ... $columns ) {

            $name = "PRIMARY";

            $this->index( $name, ... $columns );
            $this->indecies[$name]->primary();

            return $this;
        }


        public function spatialIndex( $name, ... $columns ) {
            $this->index( $name, ... $columns );
            $this->indecies[$name]->spatial();

            return $this;
        }

        /**
         * drops index by name
         * @param type $name
         * @return $this
         */
        public function dropIndex( $name ) {

            $index = new SchemaIndex( $name, $this );
            $index->drop();

            $this->indecies[$name] = $index;

            return $this;
        }

        /**
         * returns mysql definiton for indicies
         * @return mixed
         */
        public function getIndicesNames() {

            $sql = "SELECT DISTINCT TABLE_NAME, INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS where
                     TABLE_SCHEMA = '{$this->mainDatabase->getCurrentDatabase()}' AND TABLE_NAME = '{$this->tableName}'";

            $res = $this->mainDatabase->query( $sql );
            return $res->fetchAll( PDO::FETCH_ASSOC );
        }

        /**
         * returns mysql definiton for indicies
         * @return mixed
         */
        public function getIndicesStructure() {

            $sql = "SELECT INDEX_NAME, COLUMN_NAME, NON_UNIQUE FROM INFORMATION_SCHEMA.STATISTICS where
                     TABLE_SCHEMA = '{$this->mainDatabase->getCurrentDatabase()}' AND TABLE_NAME = '{$this->tableName}' ORDER BY SEQ_IN_INDEX";

            $res = $this->mainDatabase->query( $sql );
            return $res->fetchAll( PDO::FETCH_ASSOC );
        }

        public function hasIndex( $name ) {

            foreach ( $this->getIndicesNames() as $index ) {
                if ( $index["INDEX_NAME"] == $name ) {
                    return true;
                }
            }

            return false;
        }

        public function dropAllNonPrimaryIndices() {

            foreach ( $this->getIndicesNames() as $index ) {

                if ( $index["INDEX_NAME"] != "PRIMARY" ) {
                    $this->dropIndex( $index["INDEX_NAME"] );
                }
            }

            return $this;
        }

//        /**
//         *
//         * @return static
//         */
//        public function convertToMyISAM() {
//            $this->pendingOperations[] = "ALTER TABLE {$this->tableName} ENGINE = MyISAM;";
//            return $this;
//        }
//
//        /**
//         *
//         * @return $this
//         */
//        public function convertToInnoDB() {
//
//            $this->pendingOperations[] = "ALTER TABLE {$this->tableName} ENGINE = InnoDB;";
//            return $this;
//        }

    }
