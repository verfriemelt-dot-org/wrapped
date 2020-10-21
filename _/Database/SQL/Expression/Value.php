<?php namespace Wrapped\_\Database\SQL\Expression;

use \Wrapped\_\Database\SQL\QueryPart;

    class Value
    implements ExpressionItem, QueryPart {

        protected $value;

        public function __construct( $value ) {
            $this->value = $value;
        }

        public function stringify(): string {
            return $this->value;
        }

    }