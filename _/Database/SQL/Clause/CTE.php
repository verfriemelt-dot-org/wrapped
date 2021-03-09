<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL\Clause;

    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Clause;
    use \verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\QueryPart;
    use \verfriemelt\wrapped\_\Database\SQL\Statement;

    class CTE
    extends QueryPart
    implements Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "WITH %s%s";

        public array $with = [];

        private bool $recursive = false;

        public function getWeight(): int {
            return 5;
        }

        public function with( Identifier $ident, Statement $stmt ): static {

            $this->addChild( $ident );
            $this->addChild( $stmt );

            $this->with[] = [ "ident" => $ident, "stmt" => $stmt ];
            return $this;
        }

        public function recursive( bool $bool = true ): static {
            $this->recursive = $bool;
            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::CLAUSE,
                $this->recursive ? 'RECURSIVE ' : '',
                implode( ', ', array_map( fn( $o ) => "{$o['ident']->stringify( $driver )} AS ( {$o['stmt']->stringify( $driver )} )", $this->with ) ),
            );
        }

    }
