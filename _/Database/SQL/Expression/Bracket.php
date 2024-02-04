<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Expression;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use Override;

class Bracket extends Expression
{
    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            '( %s )',
            parent::stringify($driver),
        ) . $this->stringifyAlias($driver);
    }
}
