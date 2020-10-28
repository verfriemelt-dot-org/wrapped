<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Alias;
    use \Wrapped\_\Database\SQL\Aliasable;
    use \Wrapped\_\Database\SQL\QueryPart;
    use \Wrapped\_\DataModel\DataModel;

    class Identifier
    extends QueryPart
    implements ExpressionItem, Aliasable {

        use Alias;

        protected $parts = [];

        public function __construct( ... $parts ) {

            // filter out null values
            $parts = array_filter( $parts, fn( $p ) => !is_null( $p ) );

            // validation
            if ( count( $parts ) === 0 || count( $parts ) > 3 ) {
                throw new Exception( 'illegal identifier to much or less identifier' );
            }

            foreach ( $parts as $part ) {
                if ( strlen( $part ) === 0 ) {
                    throw new Exception( 'illegal identifier' );
                }
            }

            $this->parts = array_values( $parts );
        }

        public function quote( string $ident, DatabaseDriver $driver = null ): string {

            if ( !$driver ) {
                return $ident;
            }

            return $driver->quoteIdentifier( $ident );
        }

        protected function translateField( string $ident, string $table = null ) {

            if ( $ident === '*' || count( $this->context ) === 0 ) {
                return $ident;
            }

            $translations = array_map( function( DataModel $context ) use ( $ident, $table ) {
                try {

                    if ( $table !== null && $context->getTableName() !== $table ) {
                        return null;
                    }

                    return $context::translateFieldName( $ident );
                } catch ( \Exception $e ) {
                    return null;
                }
            }, $this->context );

            // filter null values;
            $translations = array_values( array_filter( $translations ) );

            switch ( count( $translations ) ) {
                case 0: return $ident;
                case 1: return $translations[0]->getNamingConvention()->getString();
                default:
                    throw new \Exception( "field ambiguous: {$ident}" );
            }
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            switch ( count( $this->parts ) ) {
                case 3:

                    [$schema, $table, $column] = $this->parts;

                    $parts = [
                        $schema,
                        $table,
                        $this->translateField( $column, $table )
                    ];

                    break;
                case 2:

                    [$table, $column] = $this->parts;

                    $parts = [
                        $table,
                        $this->translateField( $column, $table )
                    ];

                    break;
                case 1:

                    [$column] = $this->parts;

                    $parts = [
                        $this->translateField( $column )
                    ];

                    break;
            }

            return implode(
                    '.',
                    array_map(
                        fn( string $p ) => $p !== '*' ? $this->quote( $p, $driver ) : '*',
                        $parts
                    )
                ) . $this->stringifyAlias( $driver );
        }

    }
