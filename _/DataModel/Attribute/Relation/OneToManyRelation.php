<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Relation;

    #[ \Attribute ]
    class OneToManyRelation
    {
        public string $leftColumn;

        public string $rightColumn;

        public string $rightClass;

        public function __construct(string $leftColumn, string $rightColumn, string $rightClass)
        {
            $this->leftColumn = $leftColumn;
            $this->rightColumn = $rightColumn;
            $this->rightClass = $rightClass;
        }
    }
