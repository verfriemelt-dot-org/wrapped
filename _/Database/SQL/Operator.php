<?php

    namespace Wrapped\_\Database\SQL;

    class Operator
    implements ExpressionItem {

        public const OPTERATORS = [
            '+',
            '-',
            '*',
            '/',
            '~',
            '~*',
            '~~', 'like',
            '~~*', 'ilike',
        ];

        private string $operator;

        public function __construct( string $op ) {
            if ( !in_array( $op, static::OPTERATORS ) ) {
                throw new Exception( 'illegal operator' );
            }

            $this->operator = $op;
        }

        public function stringify(): string {
            return $this->operator;
        }

    }
