<?php namespace Wrapped\_\Database;

    class Order {

        private $direction;
        private $column;
        private $table = null;

        public function __construct( $column , $direction, $table = null ) {
            $this->column = $column;
            $this->direction = $direction;
            $this->table = $table;
        }

        public function fetchOrderString() {

            $str = $this->table ? "`{$this->table}`." : "";
            $str .= "`{$this->column}` {$this->direction}";

            return $str;
        }
    }
