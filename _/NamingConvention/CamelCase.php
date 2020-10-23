<?php

    namespace Wrapped\_\NamingConvention;

    class CamelCase
    extends Convention {

        public string $str;

        public function fetchStringParts(): array {

            return array_map( 'strtolower', preg_split( '/(?=[A-Z])/', $this->str ) );
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
