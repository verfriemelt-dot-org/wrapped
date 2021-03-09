<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL\Clause;

    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Clause;
    use \verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \verfriemelt\wrapped\_\Database\SQL\QueryPart;

    class From
    extends QueryPart
    implements Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "FROM %s";

        private ExpressionItem $source;

        public function getWeight(): int {
            return 20;
        }

        public function __construct( ExpressionItem $source ) {

            $this->source = $this->wrap( $source );
            $this->addChild( $this->source );
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::CLAUSE,
                $this->source->stringify( $driver )
            );
        }

    }
