<?php

    namespace Wrapped\_\Database\SQL;

    class Delete
    extends Command {

        const VERB = 'DELETE';

        public function compile(): string {
            return
                static::VERB . " " .
                "FROM " . $this->table;
        }

    }
