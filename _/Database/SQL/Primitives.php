<?php

    namespace Wrapped\_\Database\SQL;

    class Primitives
    implements ExpressionItem {

        public const PRIMITIVES = [
            true,
            false,
            null
        ];

        private $primitive;

        public function __construct( $primitive ) {
            $this->primitive = $primitive;
        }

        public function stringify(): string {
            return $this->primitive;
        }

    }
