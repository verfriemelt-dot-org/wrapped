<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Command;

use Exception;
use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use verfriemelt\wrapped\_\Database\SQL\Statement;

class Insert extends QueryPart implements Command, CommandExpression
{
    private const COMMAND = 'INSERT INTO %s ( %s )';

    private array $columns = [];

    private ?Statement $query = null;

    private Identifier $into;

    public function __construct(Identifier $ident)
    {
        $this->into = $ident;
    }

    public function getWeight(): int
    {
        return 10;
    }

    public function add(Identifier ...$column)
    {
        foreach ($column as $col) {
            $this->addChild($col);
        }

        $this->columns = [
            ...$this->columns,
            ...$column,
        ];

        return $this;
    }

    public function addQuery(Statement $stmt): static
    {
        $this->addChild($stmt);
        $this->query = $stmt;
        return $this;
    }

    public function stringify(DatabaseDriver $driver = null): string
    {
        if (count($this->columns) === 0) {
            throw new Exception('empty insert into statement');
        }

        return sprintf(
            static::COMMAND,
            $this->into->stringify($driver),
            implode(
                ', ',
                array_map(fn (ExpressionItem $i) => $i->stringify($driver), $this->columns)
            )
        ) . ($this->query ? ' ' . $this->query->stringify($driver) : '');
    }
}
