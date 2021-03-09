<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

    #[ \Attribute ]
    class LowerCase
    extends Convention {

        public CONST DESTRUCTIVE = true;

        public string $str;

        public function fetchStringParts(): array {
            return [ $this->string ];
        }

        public static function fromStringParts( string ... $parts ): Convention {
            return new static( implode( '', array_map( 'strtolower', $parts ) ) );
        }

    }
