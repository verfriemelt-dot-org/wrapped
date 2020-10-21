<?php

    namespace Wrapped\_\Database\SQL\Command;

    use \Wrapped\_\Database\SQL\Command\Command;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;

    class Select
    implements Command {

        private const COMMAND = 'SELECT %s';

        private array $items = [];

        public function add( ExpressionItem $item ) {
            $this->items[] = $item;
            return $this;
        }

        public function stringify(): string {

            return sprintf(
                static::COMMAND,
                implode(
                    ", ",
                    array_map( fn( ExpressionItem $i ) => $i->stringify(), $this->items )
                )
            );
        }

    }
