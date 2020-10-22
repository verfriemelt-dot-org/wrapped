<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Wrapped\_\Database\SQL\Alias;
    use \Wrapped\_\Database\SQL\Aliasable;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Value
    implements ExpressionItem, QueryPart, Aliasable {

        use Alias;

        protected $value;

        public function __construct( $value ) {
            $this->value = $value;
        }

        public function stringify(): string {
            return $this->value . $this->stringifyAlias();
        }

    }
