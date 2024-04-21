<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Command;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Delete extends QueryPart implements Command
{
    private const string COMMAND = 'DELETE FROM %s';

    private readonly Identifier $table;

    public function __construct(Identifier $table)
    {
        $this->table = $table;
    }

    #[Override]
    public function getWeight(): int
    {
        return 10;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::COMMAND,
            $this->table->stringify($driver),
        );
    }
}
