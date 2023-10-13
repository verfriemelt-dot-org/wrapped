<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Exception\Database;

use verfriemelt\wrapped\_\Exception\CoreException;

class DatabaseException extends CoreException
{
    public string $sqlState;

    public function setSqlState(string $state): static
    {
        $this->sqlState = $state;
        return $this;
    }
}
