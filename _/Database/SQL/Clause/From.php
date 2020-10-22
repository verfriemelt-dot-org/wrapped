<?php

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\SQL\Clause\Clause;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class From
    implements QueryPart, Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "FROM %s";

        private ExpressionItem $source;

        public function __construct( ExpressionItem $source ) {
            $this->source = $this->wrap( $source );
        }

        public function stringify(): string {

            return sprintf(
                static::CLAUSE,
                $this->source->stringify()
            );
        }

    }
