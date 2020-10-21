<?php namespace Wrapped\_\Database\SQL\Expression;

    class Value
    implements ExpressionItem {

        protected $value;

        public function __construct( $value ) {
            $this->value = $value;
        }

        public function stringify(): string {
            return $this->value;
        }

    }