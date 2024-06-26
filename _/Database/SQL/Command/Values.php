<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Command;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Values extends QueryPart implements Command, CommandExpression
{
    use CommandWrapperTrait;

    private const string COMMAND = 'VALUES ( %s )';

    private array $expressions = [];

    #[Override]
    public function getWeight(): int
    {
        return 15;
    }

    public function add(QueryPart $item): Values
    {
        // wrap in brackets if need be
        $exp = $this->wrap($item);

        $this->addChild($exp);

        $this->expressions[] = $exp;
        return $this;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::COMMAND,
            implode(
                ', ',
                array_map(fn (ExpressionItem $i) => $i->stringify($driver), $this->expressions),
            ),
        );
    }
}
