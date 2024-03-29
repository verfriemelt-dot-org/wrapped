<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Union extends QueryPart implements Clause
{
    final public const string CLAUSE = 'UNION ALL';

    #[Override]
    public function getWeight(): int
    {
        return 10000;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return 'UNION ALL';
    }
}
