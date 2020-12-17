<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Conjunction
    extends QueryPart
    implements ExpressionItem {

        public const OPTERATORS = [
            'and',
            'or',
        ];

        protected string $operator;

        public function __construct( string $op ) {

            if ( !in_array( strtolower( $op ), static::OPTERATORS ) ) {
                throw new Exception( "illegal conjunction: »{$op}«" );
            }

            $this->operator = $op;
        }

        public function stringify( DatabaseDriver $driver = null ): string {
            return strtoupper( $this->operator );
        }

    }
