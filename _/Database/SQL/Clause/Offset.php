<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Clause\Clause;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Offset
    extends QueryPart
    implements Clause {

        public const CLAUSE = "OFFSET %s";

        protected ExpressionItem $offset;

        public function getWeight(): int {
            return 70;
        }

        public function __construct( ExpressionItem $offset ) {
            $this->addChild( $offset );
            $this->offset = $offset;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::CLAUSE,
                $this->offset->stringify( $driver )
            );
        }

    }
