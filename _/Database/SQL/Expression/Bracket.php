<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Expression;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;

class Bracket extends Expression
{
    public function stringify(DatabaseDriver $driver = null): string
    {
        return sprintf(
            '( %s )',
            parent::stringify($driver),
        ) . $this->stringifyAlias($driver);
    }
}
