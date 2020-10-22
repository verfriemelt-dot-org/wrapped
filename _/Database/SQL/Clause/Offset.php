<?php

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\SQL\Clause\Clause;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Offset
    extends QueryPart
    implements Clause {

        public const CLAUSE = "OFFSET %s";

        protected ExpressionItem $offset;

        public function __construct( ExpressionItem $offset ) {
            $this->offset = $offset;
        }

        public function stringify(): string {

            return sprintf(
                static::CLAUSE,
                $this->offset->stringify()
            );
        }

    }
