<?php

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Limit
    extends QueryPart
    implements Clause {

        public const CLAUSE = "LIMIT %s";

        protected ExpressionItem $limit;

        public function __construct( ExpressionItem $limit ) {
            $this->addChild( $limit );
            $this->limit = $limit;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::CLAUSE,
                $this->limit->stringify()
            );
        }

    }
