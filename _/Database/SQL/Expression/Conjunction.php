<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Expression;

    use Exception;
    use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use verfriemelt\wrapped\_\Database\SQL\QueryPart;

    class Conjunction extends QueryPart implements ExpressionItem
    {
        public const OPTERATORS = [
            'and',
            'or',
        ];

        protected string $operator;

        public function __construct(string $op)
        {
            if (!in_array(strtolower($op), static::OPTERATORS)) {
                throw new Exception("illegal conjunction: »{$op}«");
            }

            $this->operator = $op;
        }

        public function stringify(DatabaseDriver $driver = null): string
        {
            return strtoupper($this->operator);
        }
    }
