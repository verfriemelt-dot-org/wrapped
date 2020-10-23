<?php

    namespace Wrapped\_\NamingConvention;

    #[\Attribute]
    class LowerCase
    extends Convention {

        public CONST DESTRUCTIVE = true;

        public string $str;

        public function fetchStringParts(): array {
            return [ $this->str ];
        }

        public static function fromStringParts( string ... $parts ): Convention {
            return new static( implode( '', array_map( 'strtolower', $parts ) ) );
        }

    }
