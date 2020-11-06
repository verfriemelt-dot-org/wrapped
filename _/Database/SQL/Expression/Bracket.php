<?php

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
