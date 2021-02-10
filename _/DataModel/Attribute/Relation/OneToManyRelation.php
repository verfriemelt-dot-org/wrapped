<?php

    namespace Wrapped\_\DataModel\Attribute\Relation;

    #[ \Attribute ]
    class OneToManyRelation {

        public string $leftColumn;

        public string $rightColumn;

        public string $rightClass;

        public function __construct( string $leftColumn, string $rightColumn, string $rightClass ) {
            $this->leftColumn  = $leftColumn;
            $this->rightColumn = $rightColumn;
            $this->rightClass  = $rightClass;
        }

    }
    