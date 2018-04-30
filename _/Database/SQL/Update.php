<?php

    namespace Wrapped\_\Database\SQL;

    class Update
    extends Command {

        const VERB = 'UPDATE';

        protected $items = [];

        public function all(): Select {
            $this->items[] = "*";
            return $this;
        }

        public function update( string $column, $value ): Update {

            $bindName = "bind" . count( $this->bindings );

            $this->items[]             = $this->db->quoteIdentifier( $column ) . " = :{$bindName}";
            $this->bindings[$bindName] = $value;

            return $this;
        }

        public function compile(): string {
            return
                static::VERB . " " . $this->table .
                "SET " . implode( ",", $this->items ) .
                $this->fetchLogic();
        }

    }
