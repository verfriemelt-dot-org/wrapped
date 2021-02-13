<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Expression;

    use \Wrapped\_\Database\Driver\DatabaseDriver;

    class Bracket
    extends Expression {

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                    "( %s )",
                    parent::stringify( $driver ),
                ) . $this->stringifyAlias( $driver );
        }

    }
