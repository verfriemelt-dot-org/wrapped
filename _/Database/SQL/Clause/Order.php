<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
use verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Order extends QueryPart implements Clause
{
    use CommandWrapperTrait;

    final public const CLAUSE = 'ORDER BY %s';

    private array $expressions = [];

    #[Override]
    public function getWeight(): int
    {
        return 55;
    }

    public function add(QueryPart $source, string $direction = 'ASC')
    {
        $wrap = (new Expression())
            ->add($this->wrap($source))
            ->add(new Operator($direction));

        $this->addChild($wrap);

        $this->expressions[] = $wrap;

        return $this;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            implode(
                ', ',
                array_map(fn (ExpressionItem $i) => $i->stringify($driver), $this->expressions)
            )
        );
    }
}
