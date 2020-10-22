<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\SQL\Clause\Clause;
    use \Wrapped\_\Database\SQL\Command\Command;

    class Statement
    extends QueryPart {

        private Command $command;

        private array $clauses = [];

        public function __construct( Command $command ) {
            $this->addChild( $command );
            $this->command = $command;
        }

        public function add( QueryPart $clause ) {
            $this->addChild( $clause );
            $this->clauses[] = $clause;
            return $this;
        }

        public function stringify(): string {
            return trim(
                $this->command->stringify() . " " .
                implode(
                    " ",
                    array_map( fn( QueryPart $i ) => $i->stringify(), $this->clauses )
                )
            );
        }

    }
