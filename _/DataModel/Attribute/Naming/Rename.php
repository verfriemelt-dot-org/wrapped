<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

    #[ \Attribute ]
    class Rename
    {
        public string $name;

        public function __construct(string $name)
        {
            $this->name = $name;
        }
    }
