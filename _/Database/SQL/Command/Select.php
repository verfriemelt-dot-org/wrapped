<?php

    namespace Wrapped\_\Database\SQL\Command;

    use \Wrapped\_\Database\SQL\Command\Command;
    use \Wrapped\_\Database\SQL\Command\CommandExpression;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Select
    extends QueryPart
    implements Command, CommandExpression {

        use CommandWrapperTrait;

        private const COMMAND = 'SELECT %s';

        private array $expressions = [];

        public function add( ExpressionItem $item ) {

            $expression = $this->wrap( $item );

            $this->addChild( $expression );

            $this->expressions[] = $expression;
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
