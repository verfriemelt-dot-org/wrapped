<?php

    namespace Wrapped\_\Database\SQL\Logic;

    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\Driver;

    abstract class LogicItem {

        public $tableName;
        private $value;
        private $next;
        private $prev;

        public function __construct( $value ) {
            $this->value = $value;
        }

        public function getValue() {
            return $this->value;
        }

        /**
         * @return LogicItem | null
         */
        public function getNext() {
            return $this->next;
        }

        /**
         * @return LogicItem | null
         */
        public function getPrev() {
            return $this->prev;
        }

        /**
         *
         * @param LogicItem $next
         * @return LogicItem
         */
        public function setNext( LogicItem $next ) {
            $this->next = $next;
            return $this;
        }

        /**
         *
         * @param LogicItem $prev
         * @return LogicItem
         */
        public function setPrev( LogicItem $prev ) {
            $this->prev = $prev;
            return $this;
        }

        public function setTableName( $tableName ) {
            $this->tableName = $tableName;
            return $this;
        }

        public function fetchSqlString( DbLogic $logic, Driver $driver ) {
            return $this->getValue();
        }

    }