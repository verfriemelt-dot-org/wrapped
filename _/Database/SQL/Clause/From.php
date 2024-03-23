<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Clause;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class From extends QueryPart implements Clause
{
    use CommandWrapperTrait;

    final public const string CLAUSE = 'FROM %s';

    private readonly QueryPart $source;

    #[Override]
    public function getWeight(): int
    {
        return 20;
    }

    public function __construct(QueryPart $source)
    {
        $this->source = $this->wrap($source);
        $this->addChild($this->source);
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return sprintf(
            static::CLAUSE,
            $this->source->stringify($driver)
        );
    }
}
