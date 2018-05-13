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
            $this->items[] = $this->db->quoteIdentifier( $column ) . $this->alias( $alias );
            return $this;
        }

        public function compile(): string {
            return
                static::VERB . " " .
                implode( ",", $this->items ) . " " .
                "FROM " . $this->table .
                $this->fetchLogic();
        }

        private function alias( string $alias = null ): string {

            if ( $alias === null ) {
                return "";
            }

            return " as {$this->db->quoteIdentifier( $alias )}";
        }

        public function count( string $column = "*", string $alias = null ) {

            if ( $column === "*" ) {
                $this->items[] = "COUNT( * )" . $this->alias( $alias );
            } else {
                $this->items[] = "COUNT( {$this->db->quoteIdentifier( $column )} )" . $this->alias( $alias );
            }

            return $this;
        }

    }
