<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\SQL\Clause\Clause;
    use \Wrapped\_\Database\SQL\Command\Command;

    class Statement
    implements QueryPart {

        private Command $command;

        private array $clauses = [];

        public function __construct( Command $command ) {
            $this->command = $command;
        }

        public function add( Clause $clause ) {
            $this->clauses[] = $clause;
            return $this;
        }

        public function stringify(): string {
            return trim(
                $this->command->stringify() . " " .
                implode(
                    " ",
                    array_map( fn( Clause $i ) => $i->stringify(), $this->clauses )
                )
            );
        }

    }
