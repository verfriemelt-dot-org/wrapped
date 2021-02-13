<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Expression;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Cast
    extends QueryPart
    implements ExpressionItem {

        protected $type;

        public function __construct( string $type ) {
            $this->type = $type;
        }

        public function stringify( DatabaseDriver $driver = null ): string {
            return "::{$this->type}";
        }

    }
