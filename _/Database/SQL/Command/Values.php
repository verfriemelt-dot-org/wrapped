<?php

    namespace Wrapped\_\Database\SQL\Command;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Command\Command;
    use \Wrapped\_\Database\SQL\Command\CommandExpression;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Values
    extends QueryPart
    implements Command, CommandExpression {

        use CommandWrapperTrait;

        private const COMMAND = 'VALUES ( %s )';

        private array $expressions = [];

        public function getWeight(): int {
            return 15;
        }

        public function add( ExpressionItem $item ) {

            // wrap in brackets if need be
            $exp = $this->wrap( $item );

            $this->addChild( $exp );

            $this->expressions[] = $exp;
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
