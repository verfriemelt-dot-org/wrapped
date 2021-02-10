<?php

    namespace Wrapped\_\DataModel\Attribute\Naming;


    #[\Attribute]
    class CamelCase
    extends Convention {

        public string $str;

        public function fetchStringParts(): array {

            return array_map( 'strtolower', preg_split( '/(?=[A-Z0-9])/', $this->str ) );
        }

        public static function fromStringParts( string ... $parts ): Convention {

            $first  = true;
            $string = '';

            foreach ( $parts as $part ) {

                if ( $first ) {
                    $string .= $part;
                } else {
                    $string .= ucfirst( $part );
                }

                $first = false;
            }

            return new static( $string );
        }

    }
