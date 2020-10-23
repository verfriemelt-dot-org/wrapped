<?php

    namespace Wrapped\_\DataModel\Attribute;

    #[\Attribute]
    class AttributeCaser {

        public const CASES = [
            'camelCase',
            'snake_case',
            'PascalCase'
        ];

        public function __contruct( string $case ) {
            if ( !in_array( $case, static::CASES ) ) {
                throw new Exception( "illegal case »{$case}«" );
            }

            $this->case = $case;
        }

    }
