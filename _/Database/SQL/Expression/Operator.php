<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Operator
    extends QueryPart
    implements ExpressionItem {

        public const OPTERATORS = [
            '=',
            '+',
            '-',
            '*',
            '/',
            '%',
            '~',
            '<',
            '>',
            '>=',
            '<=',
            '~*',
            '~~', 'like',
            '~~*', 'ilike',
            '@>', '<@',
            'and',
            'or',
            'not',
            'is',
            'is not',
            'is distrinct from',
            'asc',
            'desc',
            'asc nulls last',
            'desc nulls last',
            'asc nulls first',
            'desc nulls first',
        ];

        protected string $operator;

        public function __construct( string $op ) {



            if ( !in_array( strtolower( $op ), static::OPTERATORS ) ) {
                throw new Exception( "illegal operator: »{$op}«" );
            }

            $this->operator = $op;
        }

        public function stringify( DatabaseDriver $driver = null ): string {
            return strtoupper( $this->operator );
        }

    }