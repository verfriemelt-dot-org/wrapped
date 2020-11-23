<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Command\Command;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;

    class Statement
    extends QueryPart
    implements ExpressionItem {

        private Command $command;

        private array $parts = [];

        public function __construct( ?Command $command = null, QueryPart ... $parts ) {
            if ( $command ) {
                $this->setCommand( $command );
            }

            foreach( $parts as $part ) {
                $this->add( $part );
            }
        }

        public function setCommand( Command $command ): static {
            $this->addChild( $command );
            $this->command = $command;

            $this->parts[] = $command;
            return $this;
        }

        public function getCommand(): Command {
            return $this->command;
        }

        public function add( QueryPart $clause ) {
            $this->addChild( $clause );
            $this->parts[] = $clause;
            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            usort( $this->parts, fn( $a, $b ) => $a->getWeight() <=> $b->getWeight() );

            return trim(
                implode(
                    " ",
                    array_map( fn( QueryPart $i ) => $i->stringify( $driver ), $this->parts )
                )
            );
        }

    }
