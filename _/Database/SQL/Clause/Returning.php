<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Command\Command;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandExpression;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Returning extends QueryPart implements Command, CommandExpression
{
    use CommandWrapperTrait;

    private const string CLAUSE = 'RETURNING %s';

    private array $expressions = [];

    #[Override]
    public function getWeight(): int
    {
        return 100;
    }

    public function add(QueryPart $item)
    {
        $expression = $this->wrap($item);

        $this->addChild($expression);

        $this->expressions[] = $expression;
        return $this;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            implode(
                ', ',
                array_map(fn (QueryPart $i) => $i->stringify($driver), $this->expressions)
            )
        );
    }
}
