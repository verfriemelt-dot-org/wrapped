<?php

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Clause\Clause;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Order
    extends QueryPart
    implements Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "ORDER BY %s";

        private $expressions = [];

        public function getWeight(): int {
            return 55;
        }

        public function add( ExpressionItem $source, string $direction = 'ASC' ) {

            $wrap = (new Expression() )
                ->add( $this->wrap( $source ) )
                ->add( new Operator( $direction ) );

            $this->addChild( $wrap );

            $this->expressions[] = $wrap;

            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::CLAUSE,
                implode(
                    ", ",
                    array_map( fn( ExpressionItem $i ) => $i->stringify( $driver ), $this->expressions )
                )
            );
        }

    }
