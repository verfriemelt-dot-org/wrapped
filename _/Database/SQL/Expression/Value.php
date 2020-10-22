<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Alias;
    use \Wrapped\_\Database\SQL\Aliasable;
    use \Wrapped\_\Database\SQL\DataBinding;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Value
    extends QueryPart
    implements ExpressionItem, Aliasable, DataBinding {

        use Alias;

        protected $value;

        protected string $binding;

        protected static int $counter = 0;

        protected string $bind = ':bind';

        protected bool $useBinding = true;

        public function __construct( $value ) {
            $this->value = $value;
            $this->bind  .= (string) ++static::$counter;
        }

        public function getBinding() {
            return $this->value;
        }

        public function useBinding( $bool = true ) {
            $this->useBinding = $bool;
            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            if ( $this->useBinding ) {
                return $this->bind . $this->stringifyAlias();
            }

            return (string) $this->value . $this->stringifyAlias();
        }

        public function fetchBindings() {
            return [ $this->bind => $this->value ];
        }

    }
