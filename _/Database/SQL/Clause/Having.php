<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;

class Having extends QueryPart implements Clause
{
    use CommandWrapperTrait;

    final public const CLAUSE = 'HAVING %s';

    private readonly QueryPart $expression;

    public function getWeight(): int
    {
        return 52;
    }

    public function __construct(QueryPart $expression)
    {
        $wrap = $this->wrap($expression);

        $this->addChild($wrap);
        $this->expression = $wrap;
    }

    public function stringify(DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            $this->expression->stringify($driver)
        );
    }
}
