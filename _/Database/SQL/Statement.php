<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\SQL\Command\Command;

    class Statement
    implements QueryPart {

        private Command $command;

        public function __construct( Command $command ) {
            $this->command = $command;
        }

        public function stringify(): string {
            return $this->command->stringify();
        }

    }
