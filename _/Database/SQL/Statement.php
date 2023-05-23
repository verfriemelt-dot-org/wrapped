<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL;

use RuntimeException;
use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Clause\ForUpdate;
use verfriemelt\wrapped\_\Database\SQL\Clause\Union;
use verfriemelt\wrapped\_\Database\SQL\Command\Command;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;

class Statement extends QueryPart implements ExpressionItem
{
    private Command $command;

    private array $parts = [];

    private bool $sortingDisabled = false;

    public function __construct(Command $command = null, QueryPart ...$parts)
    {
        if ($command) {
            $this->setCommand($command);
        }

        foreach ($parts as $part) {
            $this->add($part);
        }
    }

    public function setCommand(Command $command): static
    {
        $this->addChild($command);
        $this->command = $command;

        $this->parts[] = $command;
        return $this;
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function add(QueryPart $clause)
    {
        if ($clause instanceof Union) {
            $this->sortingDisabled = true;
        }

        if ($clause instanceof ForUpdate && !($this->command instanceof Select)) {
            $instance = ($this->command)::class;
            throw new RuntimeException("FOR UPDATE clause requires a SELECT statement, {$instance} given");
        }

        $this->addChild($clause);
        $this->parts[] = $clause;
        return $this;
    }

    public function stringify(DatabaseDriver $driver = null): string
    {
        if (!$this->sortingDisabled) {
            usort($this->parts, fn ($a, $b) => $a->getWeight() <=> $b->getWeight());
        }

        return trim(
            implode(
                ' ',
                array_map(fn (QueryPart $i) => $i->stringify($driver), $this->parts)
            )
        );
    }
}
