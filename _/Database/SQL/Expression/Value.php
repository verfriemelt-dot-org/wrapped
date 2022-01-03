<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\SQL\Alias;
    use \verfriemelt\wrapped\_\Database\SQL\Aliasable;
    use \verfriemelt\wrapped\_\Database\SQL\DataBinding;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \verfriemelt\wrapped\_\Database\SQL\QueryPart;
    use \verfriemelt\wrapped\_\DataModel\PropertyObjectInterface;

    class Value
    extends QueryPart
    implements ExpressionItem, Aliasable, DataBinding {

        use Alias;

        protected $value;

        protected string $binding;

        protected static int $counter = 0;

        protected string $bind = ':bind';

        public function __construct( mixed $value ) {

            if ( is_object( $value ) && $value instanceof PropertyObjectInterface ) {
                $this->value = $value->dehydrateToString();
            } else {
                $this->value = $value;
            }

            $this->bind .= (string) ++static::$counter;
        }

        public function getBinding() {
            return $this->value;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            if ( $driver ) {
                return $this->bind . $this->stringifyAlias( $driver );
            }

            $type = is_object( $this->value ) ? ($this->value)::class : gettype( $this->value );

            switch ( $type ) {


                case 'array':
                    $values = array_map( fn( $e ) => (new Value( $e ) )->stringify(), $this->value );
                    $value  = "{" . implode( ',', $values ) . "}";
                    break;

                case 'integer':
                case 'float':
                case 'double':
                    $value = $this->value;
                    break;

                case 'boolean':
                    $value = $this->value ? 'true' : 'false';
                    break;

                case 'string':

                    if ( $this->value === '' ) {
                        $value = '';
                        break;
                    }

                    $value = str_replace( "'", "''", $this->value );
                    $value = "'{$value}'";

                    break;

                case 'NULL':
                    $value = 'NULL';
                    break;

                default: throw new Exception( "unsupported type: " . gettype( $this->value ) );
            }

            return $value . $this->stringifyAlias( $driver );
        }

        public function fetchBindings(): array {
            return [ $this->bind => $this->value ];
        }

    }
