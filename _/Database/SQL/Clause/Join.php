<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Join extends QueryPart implements Clause
{
    use CommandWrapperTrait;

    final public const string CLAUSE = 'JOIN %s ON %s';

    private readonly ExpressionItem $source;

    private readonly ExpressionItem $on;

    #[Override]
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

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            $this->source->stringify($driver),
            $this->on->stringify($driver),
        );
    }
}
