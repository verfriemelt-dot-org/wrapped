<?php

    namespace Wrapped\_\NamingConvention;

    class SpaceCase
    extends Convention {

        protected string $str;

        public function fetchStringParts(): array {
            return explode( ' ', $this->str );
        }

        public static function fromStringParts( string ...$parts ): Convention {
            return new static( implode( ' ', array_map( 'strtolower', $parts ) ) );
        }

    }
