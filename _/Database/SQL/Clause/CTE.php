<?php

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Clause\Clause;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\QueryPart;
    use \Wrapped\_\Database\SQL\Statement;

    class CTE
    extends QueryPart
    implements Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "WITH %s";

        public array $with = [];

        public function getWeight(): int {
            return 5;
        }

        public function with( Identifier $ident, Statement $stmt ): static {

            $this->addChild( $ident );
            $this->addChild( $stmt );

            $this->with[] = [ "ident" => $ident, "stmt" => $stmt ];
            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::CLAUSE,
                implode( ', ', array_map( fn( $o ) => "{$o['ident']->stringify( $driver )} AS ( {$o['stmt']->stringify( $driver )} )", $this->with ) ),
            );
        }

    }
