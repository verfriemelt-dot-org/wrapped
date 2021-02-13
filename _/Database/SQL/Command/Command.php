<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Command;

    interface Command {

        public function getWeight(): int;
    }
