<?php

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Clause\Clause;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class From
    extends QueryPart
    implements Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "FROM %s";

        private ExpressionItem $source;

        public function __construct( ExpressionItem $source ) {
            $this->source = $this->wrap( $source );
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::CLAUSE,
                $this->source->stringify()
            );
        }

    }
