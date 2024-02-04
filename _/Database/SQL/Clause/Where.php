<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Where extends QueryPart implements Clause
{
    use CommandWrapperTrait;

    final public const CLAUSE = 'WHERE %s';

    public Expression $expression;

    #[Override]
    public function getWeight(): int
    {
        return 40;
    }

    public function __construct(QueryPart $args)
    {
        if (!($args instanceof Expression)) {
            $exp = new Expression($args);
        } else {
            $exp = $args;
        }

        $wrap = $this->wrap($exp);

        $this->addChild($wrap);

        /* @phpstan-ignore-next-line */
        $this->expression = $wrap;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            $this->expression->stringify($driver)
        );
    }
}
