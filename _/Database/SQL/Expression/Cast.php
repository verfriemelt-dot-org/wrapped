<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Expression;

    use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use verfriemelt\wrapped\_\Database\SQL\QueryPart;

    class Cast extends QueryPart implements ExpressionItem
    {
        protected $type;

        public function __construct(string $type)
        {
            $this->type = $type;
        }

        public function stringify(DatabaseDriver $driver = null): string
        {
            return "::{$this->type}";
        }
    }
