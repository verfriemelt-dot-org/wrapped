<?php

    namespace Wrapped\_\Database\SQL;

    class Insert
    extends Command {

        const VERB = 'INSERT INTO';

        protected $columns = [], $values  = [];

        public function insert( string $column, $value ): Insert {

            $bindName = "ibind" . count( $this->bindings );

            $this->columns[]           = $this->db->quoteIdentifier( $column );
            $this->values[]            = ":{$bindName}";
            $this->bindings[$bindName] = $value;

            return $this;
        }

        public function compile(): string {
            return
                static::VERB . " " . $this->table . ' (' . implode( ',', $this->columns ) . ') ' .
                "VALUES (" . implode( ",", $this->values ) . ")" .
                $this->fetchLogic();
        }

    }
