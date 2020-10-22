<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Alias;
    use \Wrapped\_\Database\SQL\Aliasable;

    class Bracket
    extends Expression
    implements Aliasable {

        use Alias;

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                    "( %s )",
                    parent::stringify(),
                ) . $this->stringifyAlias();
        }

    }
