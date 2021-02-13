<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Command\Command;
    use \Wrapped\_\Database\SQL\Command\CommandExpression;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Returning
    extends QueryPart
    implements Command, CommandExpression {

        use CommandWrapperTrait;

        private const CLAUSE = 'RETURNING %s';

        private array $expressions = [];

        public function getWeight(): int {
            return 100;
        }

        public function add( ExpressionItem $item ) {

            $expression = $this->wrap( $item );

            $this->addChild( $expression );

            $this->expressions[] = $expression;
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
