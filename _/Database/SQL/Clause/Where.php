<?php

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Where
    implements QueryPart, Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "WHERE %s";

        private ExpressionItem $expression;

        public function __construct( ExpressionItem $expression ) {
            $this->expression = $this->wrap( $expression );
        }

        public function stringify(): string {

            return sprintf(
                static::CLAUSE,
                $this->expression->stringify()
            );
        }

    }
