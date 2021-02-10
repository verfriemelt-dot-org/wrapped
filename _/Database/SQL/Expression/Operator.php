<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Operator
    extends QueryPart
    implements ExpressionItem {

        public const OPTERATORS = [
            '=',
            '!=',
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
            '!~*',
            '!~',
            '~~', 'like',
            '~~*', 'ilike',
            '<->',
            '@>', '<@',
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
            'distinct',
            'is true',
            'is false',
            'is null',
            'is not true',
            'is not false',
            'is not null',
            'is distinct from true',
            'is distinct from false',
            'is distinct from null',
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
