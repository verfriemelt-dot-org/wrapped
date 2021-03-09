<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL\Command;

    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Command;
    use \verfriemelt\wrapped\_\Database\SQL\Command\CommandExpression;
    use \verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \verfriemelt\wrapped\_\Database\SQL\QueryPart;

    class Select
    extends QueryPart
    implements Command, CommandExpression {

        use CommandWrapperTrait;

        private const COMMAND = 'SELECT %s';

        private array $expressions = [];

        public function __construct( ExpressionItem ... $items ) {
            array_map( fn( $i ) => $this->add( $i ), $items );
        }

        public function getWeight(): int {
            return 10;
        }

        public function add( ExpressionItem ... $item ) {

            foreach ( $item as $i ) {

                $expression = $this->wrap( $i );

                $this->addChild( $expression );
                $this->expressions[] = $expression;
            }

            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::COMMAND,
                implode(
                    ", ",
                    array_map( fn( ExpressionItem $i ) => $i->stringify( $driver ), $this->expressions )
                )
            );
        }

    }
