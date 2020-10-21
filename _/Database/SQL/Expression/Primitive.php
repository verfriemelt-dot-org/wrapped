<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \TheSeer\Tokenizer\Exception;

    class Primitive
    implements ExpressionItem {

        public const PRIMITIVES = [
            true,
            false,
            null
        ];

        protected $primitive;

        public function __construct( $primitive ) {

            if ( !is_bool( $primitive ) && !is_null( $primitive ) ) {
                throw new Exception( 'not a primitive' );
            }

            $this->primitive = $primitive;
        }

        public function stringify(): string {

            if ( is_bool( $this->primitive ) ) {
                return $this->primitive ? 'true' : 'false';
            }

            return 'null';
        }

    }
