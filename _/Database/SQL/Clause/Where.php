<?php

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Where
    implements QueryPart {

        public const CLAUSE = "WHERE %s";

        private ExpressionItem $expression;

        public function __construct( ExpressionItem $expression ) {
            $this->expression = $expression;
        }

        public function stringify(): string {

            return sprintf(
                static::CLAUSE,
                $this->expression->stringify()
            );
        }

    }
