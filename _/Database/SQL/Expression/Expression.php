<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Alias;
    use \Wrapped\_\Database\SQL\Aliasable;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Expression
    extends QueryPart
    implements ExpressionItem, Aliasable {

        use CommandWrapperTrait;
        use Alias;

        protected array $expressions = [];

        public function __construct( ExpressionItem ... $args ) {
            foreach ( $args as $arg ) {
                $this->add( $arg );
            }
        }

        //Identifier | Primitives | Operator
        public function add( ExpressionItem ... $expressions ) {

            foreach ( $expressions as $expression ) {
                $this->addChild( $expression );
                $this->expressions[] = $expression;
            }

            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            if ( count( $this->expressions ) === 0 ) {
                throw new Exception( 'empty expression' );
            }

            return implode(
                    " ",
                    array_map( fn( ExpressionItem $i ) => $i->stringify( $driver ), $this->expressions )
                ) . $this->stringifyAlias( $driver );
        }

        public function fetchLast(): ?ExpressionItem {

            if ( empty( $this->expressions ) ) {
                return null;
            }

            return $this->expressions[count( $this->expressions ) - 1];
        }

    }
