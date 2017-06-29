<?php

    namespace Wrapped\_\Database\Driver\Mysql;

    class Table {

        private $parent;
        private $joins  = [];
        private $joinHandle;
        private $select = [];
        private $tableName;
        private $where;
        private $selectionColumns;

        public function __construct( $tableName ) {
            $this->tableName = $tableName;
        }

        public function with( $table, $on ) {

            $t = (new Table( $table ) )->setJoinHandle( $this->joinHandle );
            $t->setParentTable( $this );

            $this->joins[] = [ "table" => $t, "on" => $on ];

            return $t;
        }

        /**
         * sets parent table
         * @param type $parent
         * @return Table
         */
        public function setParentTable( $parent ) {
            $this->parent = $parent;
            return $this;
        }

        /**
         *
         * @return Table
         */
        public function getParentTable() {
            return $this->parent;
        }

        public function what( array $what ) {
            $this->select = $what;
            return $this;
        }

        /**
         *
         * @param Join $join
         * @return Table
         */
        public function setJoinHandle( Join $join ) {
            $this->joinHandle = $join;
            return $this;
        }

        /**
         *
         * @return Join
         */
        public function getJoinHandle() {
            return $this->joinHandle;
        }

        /**
         * returns table name
         * @return string
         */
        public function getName() {
            return $this->tableName;
        }

        /**
         *
         * @return Table[]
         */
        public function getJoins() {
            return $this->joins;
        }

        public function setWhere( $where ) {
            $this->where = $where;
            return $this;
        }

        public function getWhere() {
            return $this->where;
        }

        public function getSelectionColumns() {
            return $this->selectionColumns;
        }

        public function setSelectionColumns( $selectionCulumns ) {
            $this->selectionColumns = $selectionCulumns;
            return $this;
        }

    }
