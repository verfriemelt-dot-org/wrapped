<?php

    namespace Wrapped\_\Database\SQL;

    class Order {

        private $direction;
        private $column;
        private $table = null;
        private $skipQuote;

        public function __construct( $column, $direction, $table = null, $skipQuote = false ) {
            $this->column    = $column;
            $this->direction = $direction;
            $this->table     = $table;

            $this->skipQuote = $skipQuote;
        }

        public function fetchOrderString( \Wrapped\_\Database\Driver\DatabaseDriver $driver ) {

            $str = $this->table ? $driver->quoteIdentifier($this->table) . "." : "";

            if ( $this->skipQuote ) {
                $str .= "{$this->column} {$this->direction}";
            } else {
                $str .= "{$driver->quoteIdentifier( $this->column )} {$this->direction}";
            }


            return $str;
        }

    }
