<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class GroupBy extends QueryPart implements Clause
{
    use CommandWrapperTrait;

    final public const string CLAUSE = 'GROUP BY %s';

    private array $expressions = [];

    public function __construct(?QueryPart $by = null)
    {
        if ($by !== null) {
            $this->add($by);
        }
    }

    #[Override]
    public function getWeight(): int
    {
        return 50;
    }

    public function add(QueryPart $source)
    {
        $wrap = (new Expression())
            ->add($this->wrap($source));

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
                array_map(fn (QueryPart $i) => $i->stringify($driver), $this->expressions),
            ),
        );
    }
}
