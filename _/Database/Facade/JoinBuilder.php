<?php

    namespace Wrapped\_\Database\Facade;

    use \Wrapped\_\Database\SQL\Clause\Join;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Operator;

    class JoinBuilder {

        private Identifier $joinedTable;

        private Expression $on;

        public function __construct( ?string ... $source ) {
            $this->joinedTable = new Identifier( ... $source );
            $this->on          = new Expression;
        }

        public function on( $sourceColumn, $destinationColumn ) {

            $source = new Identifier( ... ( is_array( $sourceColumn ) ? $sourceColumn : [ $sourceColumn ] ) );
            $dest   = new Identifier( ... ( is_array( $destinationColumn ) ? $destinationColumn : [ $destinationColumn ] ) );

            $op = new Operator( '=' );

            if ( $this->on->fetchLast() !== null && $this->on->fetchLast() instanceof Operator ) {
                $this->on->add( new Conjunction( 'and' ) );
            }

            $this->on->add( $source );
            $this->on->add( $op );
            $this->on->add( $dest );

            return $this;
        }

        public function getExpression(): Expression {
            return $this->on;
        }

        public function fetchJoinClause(): Join {
            return new Join( $this->joinedTable, $this->on );
        }

    }
