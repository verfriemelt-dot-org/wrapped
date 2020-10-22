<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \TheSeer\Tokenizer\Exception;
    use \Wrapped\_\Database\SQL\Alias;
    use \Wrapped\_\Database\SQL\Aliasable;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Primitive
    implements ExpressionItem, QueryPart, Aliasable {

        use Alias;

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

            switch ( getType( $this->primitive ) ) {
                case 'boolean': $result = $this->primitive ? 'true' : 'false';
                    break;
                default: $result = 'null';
            }

            return $result . $this->stringifyAlias();
        }

    }
