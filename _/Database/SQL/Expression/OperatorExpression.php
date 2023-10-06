<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Expression;

use Exception;
use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;

class OperatorExpression extends QueryPart implements ExpressionItem
{
    final public const OPTERATORS = [
        'exists' => [
            'minArgs' => 1,
            'maxArgs' => 1,
            'string' => 'EXISTS ( %s )',
        ],
        'in' => [
            'minArgs' => 1,
            'maxArgs' => \INF,
            'string' => 'IN ( %s )',
        ],
        'between' => [
            'minArgs' => 2,
            'maxArgs' => 2,
            'string' => 'BETWEEN %s AND %s',
        ],
    ];

    protected string $operator;

    protected array $arguments;

    public function __construct(string $op, ExpressionItem ...$args)
    {
        $op = strtolower($op);

        if (!isset(static::OPTERATORS[$op])) {
            throw new Exception('illegal operator');
        }

        if (count($args) < static::OPTERATORS[$op]['minArgs'] || count(
            $args
        ) > static::OPTERATORS[$op]['maxArgs']) {
            throw new Exception("missing arguments for operator »{$op}«");
        }

        foreach ($args as $arg) {
            $this->addChild($arg);
        }

        $this->operator = $op;
        $this->arguments = $args;
    }

    public function stringify(?DatabaseDriver $driver = null): string
    {
        return match ($this->operator) {
            'in' => sprintf(
                static::OPTERATORS[$this->operator]['string'],
                implode(', ', array_map(static fn (ExpressionItem $i) => $i->stringify($driver), $this->arguments))
            ),
            default => sprintf(
                static::OPTERATORS[$this->operator]['string'],
                ...array_map(static fn (ExpressionItem $i) => $i->stringify($driver), $this->arguments)
            ),
        };
    }
}
