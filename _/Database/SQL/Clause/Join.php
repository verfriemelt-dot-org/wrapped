<?php

    namespace Wrapped\_\Database\SQL\Clause;

use \Wrapped\_\Database\Driver\DatabaseDriver;
use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
use \Wrapped\_\Database\SQL\QueryPart;

    class Join
    extends QueryPart
    implements Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "JOIN %s ON %s";

        private ExpressionItem $source;

        private ExpressionItem $on;

        public function __construct( ExpressionItem $source, ExpressionItem $on ) {
            $this->source = $source;
            $this->on     = $on;
        }

        public function stringify(DatabaseDriver $driver = null ): string {

            return sprintf(
                static::CLAUSE,
                $this->source->stringify( $driver ),
                $this->on->stringify( $driver ),
            );
        }

    }