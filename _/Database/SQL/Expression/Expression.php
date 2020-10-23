<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Expression
    extends QueryPart
    implements ExpressionItem {

        use CommandWrapperTrait;

        protected array $expressions = [];

        //Identifier | Primitives | Operator
        public function add( ExpressionItem $expression ) {

            $this->addChild( $expression );

            $this->expressions[] = $expression;
            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            if ( count( $this->expressions ) === 0 ) {
                throw new Exception( 'empty expression' );
            }

            return implode(
                " ",
                array_map( fn( ExpressionItem $i ) => $i->stringify( $driver ), $this->expressions )
            );
        }

        public function fetchLastExpressionItem(): ?ExpressionItem {

            if ( empty( $this->expressions ) ) {
                return null;
            }

            return $this->expressions[count( $this->expressions ) - 1];
        }

    }
