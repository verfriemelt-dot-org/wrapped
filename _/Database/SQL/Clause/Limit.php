<?php

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Limit
    implements Clause, QueryPart {

        public const CLAUSE = "LIMIT %s";

        protected ExpressionItem $limit;

        public function __construct( ExpressionItem $limit ) {
            $this->limit = $limit;
        }

        public function stringify(): string {

            return sprintf(
                static::CLAUSE,
                $this->limit->stringify()
            );
        }

    }
