<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \TheSeer\Tokenizer\Exception;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Operator
    implements ExpressionItem, QueryPart {

        public const OPTERATORS = [
            '+',
            '-',
            '*',
            '/',
            '%',
            '~',
            '~*',
            '~~', 'like',
            '~~*', 'ilike',
        ];

        protected string $operator;

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
