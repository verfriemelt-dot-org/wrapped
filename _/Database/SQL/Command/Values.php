<?php

    namespace Wrapped\_\Database\SQL\Command;

    use \Wrapped\_\Database\SQL\Command\Command;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;

    class Values
    implements Command, CommandExpression {

        use CommandWrapperTrait;

        private const COMMAND = 'VALUES ( %s )';

        private array $expressions = [];

        public function add( ExpressionItem $item ) {
            $this->expressions[] = $this->wrap( $item );
            return $this;
        }

        public function stringify(): string {

            return sprintf(
                static::COMMAND,
                implode(
                    ", ",
                    array_map( fn( ExpressionItem $i ) => $i->stringify(), $this->expressions )
                )
            );
        }

    }
