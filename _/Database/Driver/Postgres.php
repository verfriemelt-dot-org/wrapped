<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\Driver;

use Override;

class Postgres extends DatabaseDriver
{
    final public const PDO_NAME = 'pgsql';

    #[Override]
    public function quoteIdentifier(string $ident): string
    {
        return sprintf('"%s"', $ident);
    }
}
