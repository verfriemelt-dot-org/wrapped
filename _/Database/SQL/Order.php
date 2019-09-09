<?php

    namespace Wrapped\_\Database\SQL;

    class Order {

        private $direction;
        private $column;
        private $table = null;

        public function __construct( $column, $direction, $table = null ) {
            $this->column    = $column;
            $this->direction = $direction;
            $this->table     = $table;
        }

        public function fetchOrderString( \Wrapped\_\Database\Driver\DatabaseDriver $driver ) {

            $str = $this->table ? $driver->quoteIdentifier($this->table) . "." : "";
            $str .= "{$driver->quoteIdentifier($this->column)} {$this->direction}";

            return $str;
        }

    }
