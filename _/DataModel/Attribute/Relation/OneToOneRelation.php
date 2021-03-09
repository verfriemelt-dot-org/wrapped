<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel\Attribute\Relation;

    #[ \Attribute ]
    class OneToOneRelation {

        public string $leftColumn;

        public string $rightColumn;

        public function __construct( string $leftColumn, string $rightColumn ) {
            $this->leftColumn  = $leftColumn;
            $this->rightColumn = $rightColumn;
        }

    }
