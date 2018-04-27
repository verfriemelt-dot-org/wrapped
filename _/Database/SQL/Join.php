<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\Mysql;

    class Join {

        /** @var Mysql */
        private $db;
        private $table;
        private $stmt;
        private $select = [];
        private $join   = [];
        private $dbLogic;

        public function __construct( Table $table, $db ) {
            $this->table = $table;
            $this->table->setJoinHandle( $this );
            $this->db    = $db;
        }

        public function with( $table, $on ) {
            return $this->table->with( $table, $on );
        }

        public function execute() {
            return $this->db->executeJoin( $this );
        }

        public function getStatement() {
            return $this->stmt;
        }

        public function prepare() {
            $this
                ->_prepareSelect()
                ->_parseSelects( $this->table )
                ->_writeSelects()
                ->_writeFrom( $this->table )
                ->_parseJoins( $this->table )
                ->_writeJoins()
                ->_writeWhere();

            return $this;
        }

        /**
         * write select
         * @return \Wrapped\_\Database\Driver\Mysql\Join
         */
        private function _prepareSelect() {
            $this->stmt = "SELECT ";
            return $this;
        }

        /**
         * cycle through all the selections
         * @param \Wrapped\_\Database\Driver\SQL\Table $table
         * @return \Wrapped\_\Database\Driver\Mysql\Join
         */
        private function _parseSelects( Table $table ) {
            $selectionColumns = $table->getSelectionColumns();
            if ( !empty( $selectionColumns ) ) {
                foreach ( $selectionColumns as $key => $column ) {

                    if ( is_string( $key ) ) {
                        $this->select[] = "{$table->getName()}.`{$key}` as `{$column}`";
                    } else {
                        $this->select[] = "{$table->getName()}.`{$column}`";
                    }
                }
            }

            $joins = $table->getJoins();

            if ( !empty( $joins ) ) {
                foreach ( $joins as $join ) {
                    $this->_parseSelects( $join["table"] );
                }
            }

            return $this;
        }

        private function _writeSelects() {
            $this->stmt .= implode( ", ", $this->select );
            return $this;
        }

        private function _writeFrom( Table $table ) {
            $this->stmt .= " FROM " . $table->getName();
            return $this;
        }

        private function _parseJoins( Table $table ) {
            $joins = $table->getJoins();
            foreach ( $joins as $join ) {
                $name         = $join["table"]->getName();
                $on           = $join["on"];
                $this->join[] = " INNER JOIN {$name} ON ({$on})";

                if ( !empty( $join["table"]->getJoins() ) ) {
                    $this->_parseJoins( $join["table"] );
                }
            }

            return $this;
        }

        private function _writeJoins() {
            $this->stmt .= " " . implode( "\n", $this->join );
            return $this;
        }

        public function mergeDbLogic( DbLogic $logic ) {

            if ( $this->dbLogic === null ) {
                $this->dbLogic = $logic;
            } else {
                $this->dbLogic->merge( $logic );
            }

            return $this;
        }

        /**
         * return parsed where data
         */
        public function getDbLogic() {
            return $this->dbLogic;
        }

        public function _writeWhere() {
            $this->stmt .= " {$this->dbLogic->getString()}";
        }

        /**
         *
         * @return Table
         */
        public function getTable() {
            return $this->table;
        }

    }
