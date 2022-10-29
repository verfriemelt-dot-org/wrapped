<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

    #[ \Attribute ]
    class SnakeCase extends Convention
    {
        public function fetchStringParts(): array
        {
            return explode('_', $this->string);
        }

        public static function fromStringParts(string ...$parts): Convention
        {
            return new static(strtolower(implode('_', $parts)));
        }
    }
