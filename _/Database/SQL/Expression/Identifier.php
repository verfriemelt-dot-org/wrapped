<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Alias;
    use \Wrapped\_\Database\SQL\Aliasable;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Identifier
    extends QueryPart
    implements ExpressionItem, Aliasable {

        use Alias;

        protected $parts = [];

        protected ?DatabaseDriver $driver = null;

        public function __construct( ... $parts ) {

            // filter out null values
            $parts = array_filter( $parts, fn( $p ) => !is_null( $p ) );

            // validation
            if ( count( $parts ) === 0 || count( $parts ) > 3 ) {
                throw new Exception( 'illegal identifier' );
            }

            foreach ( $parts as $part ) {
                if ( strlen( $part ) === 0 ) {
                    throw new Exception( 'illegal identifier' );
                }
            }

            $this->parts = $parts;
        }

        public function quote( string $ident ): string {

            if ( !$this->driver ) {
                return $ident;
            }

            return $this->driver->quoteIdentifier( $ident );
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            $this->driver = $driver;
            return implode(
                    '.',
                    array_map(
                        fn( string $p ) => $this->quote( $p ),
                        $this->parts
                    )
                ) . $this->stringifyAlias();
        }

    }
