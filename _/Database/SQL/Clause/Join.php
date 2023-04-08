<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;

class Join extends QueryPart implements Clause
{
    use CommandWrapperTrait;

    public const CLAUSE = 'JOIN %s ON %s';

    private ExpressionItem $source;

    private ExpressionItem $on;

    public function getWeight(): int
    {
        return 30;
    }

    public function __construct(ExpressionItem $source, ExpressionItem $on)
    {
        $this->addChild($source);
        $this->addChild($on);

        $this->source = $source;
        $this->on = $on;
    }

    public function stringify(DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            $this->source->stringify($driver),
            $this->on->stringify($driver),
        );
    }
}
