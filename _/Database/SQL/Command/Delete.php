<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Command;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;

class Delete extends QueryPart implements Command
{
    private const COMMAND = 'DELETE FROM %s';

    private Identifier $table;

    public function __construct(Identifier $table)
    {
        $this->table = $table;
    }

    public function getWeight(): int
    {
        return 10;
    }

    public function stringify(DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::COMMAND,
            $this->table->stringify($driver)
        );
    }
}
