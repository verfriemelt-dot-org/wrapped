<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Limit extends QueryPart implements Clause
{
    final public const CLAUSE = 'LIMIT %s';

    protected ExpressionItem $limit;

    #[Override]
    public function getWeight(): int
    {
        return 60;
    }

    public function __construct(ExpressionItem $limit)
    {
        $this->addChild($limit);
        $this->limit = $limit;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            $this->limit->stringify($driver)
        );
    }
}
