<?php

    namespace Wrapped\_\DataModel\Attribute;

    #[ \Attribute ]
    class AutoJoin {

        public string $leftColumn;

        public string $rightColumn;

        public function __construct( string $leftColumn, string $rightColumn ) {
            $this->leftColumn  = $leftColumn;
            $this->rightColumn = $rightColumn;
        }

    }
