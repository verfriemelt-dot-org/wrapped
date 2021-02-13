<?php

    declare(strict_types = 1);

    namespace Wrapped\_\DataModel\Attribute\Naming;

    #[ \Attribute ]
    class SnakeCase
    extends Convention {

        protected string $str;

        public function fetchStringParts(): array {
            return explode( '_', $this->str );
        }

        public static function fromStringParts( string ... $parts ): Convention {
            return new static( strtolower( implode( '_', $parts ) ) );
        }

    }
