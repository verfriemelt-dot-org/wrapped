<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Command\Command;

    class Statement
    extends QueryPart {

        private Command $command;

        private array $clauses = [];

        public function __construct( ?Command $command = null ) {
            if ( $command ) {
                $this->setCommand( $command );
            }
        }

        public function setCommand( Command$command ) {
            $this->addChild( $command );
            $this->command = $command;
        }

        public function getCommand(): Command {
            return $this->command;
        }

        public function add( QueryPart $clause ) {
            $this->addChild( $clause );
            $this->clauses[] = $clause;
            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            usort( $this->clauses, fn( $a, $b ) => $a->getWeight() <=> $b->getWeight() );

            return trim(
                $this->command->stringify( $driver ) . " " .
                implode(
                    " ",
                    array_map( fn( QueryPart $i ) => $i->stringify( $driver ), $this->clauses )
                )
            );
        }

    }
