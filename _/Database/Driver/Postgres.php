<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\Driver;

class Postgres extends DatabaseDriver
{
    final public const PDO_NAME = 'pgsql';

    public function quoteIdentifier(string $ident): string
    {
        return sprintf('"%s"', $ident);
    }
}
