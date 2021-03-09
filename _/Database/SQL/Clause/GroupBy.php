<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL\Clause;

    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Clause;
    use \verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \verfriemelt\wrapped\_\Database\SQL\QueryPart;

    class GroupBy
    extends QueryPart
    implements Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "GROUP BY %s";

        private $expressions = [];

        public function __construct( ExpressionItem $by = null ) {

            if ( $by ) {
                $this->add( $by );
            }
        }

        public function getWeight(): int {
            return 50;
        }

        public function add( ExpressionItem $source ) {

            $wrap = (new Expression() )
                ->add( $this->wrap( $source ) );

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
