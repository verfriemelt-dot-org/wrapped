<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

    #[ \Attribute ]
    class PascalCase
    extends Convention {

        public string $str;

        public function fetchStringParts(): array {

            return array_map( 'strtolower', preg_split( '/(?=[A-Z])/', lcfirst( $this->str ) ) );
        }

        public static function fromStringParts( string ... $parts ): Convention {

            $string = '';

            foreach ( $parts as $part ) {

                $string .= ucfirst( $part );
            }

            return new static( $string );
        }

    }
