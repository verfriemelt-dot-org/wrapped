<?php

    namespace Wrapped\_\Database\SQL\Command;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Command\Command;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Delete
    extends QueryPart
    implements Command {

        private const COMMAND = 'DELETE FROM %s';

        private Identifier $table;

        public function __construct( Identifier $table ) {
            $this->table = $table;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::COMMAND,
                $this->table->stringify( $driver )
            );
        }

    }
