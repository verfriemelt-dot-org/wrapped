<?php

    namespace Wrapped\_\Database\SQL;

    class Expression
    implements ExpressionItem {

        private array $expressions = [];

        //Identifier | Primitives | Operator
        public function add( ExpressionItem $expression ) {
            $this->expressions[] = $expression;
            return $this;
        }

        public function stringify(): string {

            if ( count( $this->expressions ) === 0 ) {
                throw new \Exception( 'empty expression' );
            }

            return implode(
                " ",
                array_map( fn( ExpressionItem $i ) => $i->stringify(), $this->expressions )
            );
        }

    }
