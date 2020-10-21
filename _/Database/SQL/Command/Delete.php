<?php

    namespace Wrapped\_\Database\SQL\Command;

    use \Wrapped\_\Database\SQL\Command\Command;
    use \Wrapped\_\Database\SQL\Identifier;

    class Delete
    implements Command {

        private const COMMAND = 'DELETE FROM %s';

        private Identifier $table;

        public function __construct( Identifier $table ) {
            $this->table = $table;
        }

        public function stringify(): string {

            return sprintf(
                static::COMMAND,
                $this->table->stringify()
            );
        }

    }
