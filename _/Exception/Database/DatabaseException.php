<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Exception\Database;

    class DatabaseException extends \verfriemelt\wrapped\_\Exception\CoreException
    {
        public string $sqlState;

        public function setSqlState(string $state): static
        {
            $this->sqlState = $state;
            return $this;
        }
    }
