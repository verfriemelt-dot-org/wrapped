<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Expression;

use Exception;
use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class Operator extends QueryPart implements ExpressionItem
{
    final public const array OPTERATORS = [
        '=',
        '!=',
        '+',
        '-',
        '*',
        '/',
        '%',
        '~',
        '<',
        '>',
        '>=',
        '<=',
        '~*',
        '!~*',
        '!~',
        '~~',
        'like',
        '~~*',
        'ilike',
        '<->',
        '@>',
        '<@',
        'not',
        'in',
        'is',
        'is not',
        'is distrinct from',
        'asc',
        'desc',
        'asc nulls last',
        'desc nulls last',
        'asc nulls first',
        'desc nulls first',
        'distinct',
        'is true',
        'is false',
        'is null',
        'is not true',
        'is not false',
        'is not null',
        'is distinct from true',
        'is distinct from false',
        'is distinct from null',
    ];

    protected string $operator;

    public function __construct(string $op)
    {
        if (!in_array(strtolower($op), static::OPTERATORS)) {
            throw new Exception("illegal operator: »{$op}«");
        }

        $this->operator = $op;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        return strtoupper($this->operator);
    }
}
