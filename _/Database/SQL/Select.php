<?php

    namespace Wrapped\_\Database\SQL;

    class Select
    extends Command {

        const VERB = 'SELECT';

        protected $items = [];

        public function all(): Select {
            $this->items[] = "*";
            return $this;
        }

        public function addColumn( string $column, $alias = null ): Select {

            $alias = "";

            if ( $alias ) {
                $alias = " as {$this->db->quoteIdentifier( $alias )}";
            }

            $this->items[] = $this->db->quoteIdentifier( $column ) . $alias;
            return $this;
        }

        public function compile(): string {
            return
                static::VERB . " " .
                implode( ",", $this->items ) . " " .
                "FROM " . $this->table;
        }

    }
