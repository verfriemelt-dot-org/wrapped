<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Offset extends QueryPart implements Clause
{
    final public const string CLAUSE = 'OFFSET %s';

    protected ExpressionItem $offset;

    #[Override]
    public function getWeight(): int
    {
        return 70;
    }

    public function __construct(ExpressionItem $offset)
    {
        $this->addChild($offset);
        $this->offset = $offset;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            $this->offset->stringify($driver)
        );
    }
}
