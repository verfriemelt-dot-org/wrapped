<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;

class ForUpdate extends QueryPart implements Clause
{
    use CommandWrapperTrait;

    final public const CLAUSE = 'FOR UPDATE %s';

    public ExpressionItem $expression;

    public function getWeight(): int
    {
        return 100;
    }

    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            ''
        );
    }
}
