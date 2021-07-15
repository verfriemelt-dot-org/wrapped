<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

    #[ \Attribute ]
    class SpaceCase
    extends Convention {

        public function fetchStringParts(): array {
            return explode( ' ', $this->string );
        }

        public static function fromStringParts( string ...$parts ): Convention {
            return new static( implode( ' ', array_map( 'strtolower', $parts ) ) );
        }

    }
