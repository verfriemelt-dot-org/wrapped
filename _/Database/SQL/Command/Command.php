<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL\Command;

    interface Command {

        public function getWeight(): int;
    }
