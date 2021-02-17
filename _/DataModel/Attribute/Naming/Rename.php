<?php

    declare(strict_types = 1);

    namespace Wrapped\_\DataModel\Attribute\Naming;

    #[ \Attribute ]
    class Rename {

        public string $name;

        public function __construct( string $name ) {
            $this->name = $name;
        }

    }
