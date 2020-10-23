<?php

    namespace Wrapped\_\Database\Facade;

    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Clause\From;
    use \Wrapped\_\Database\SQL\Clause\Limit;
    use \Wrapped\_\Database\SQL\Clause\Order;
    use \Wrapped\_\Database\SQL\Clause\Returning;
    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Command\Insert;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Command\Values;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\Database\SQL\Statement;

    class Query {

        private Statement $stmt;

        private Select $select;

        private Insert $insert;

        private Values $values;

        private From $from;

        private Where $where;

        private Returning $returning;

        private Order $order;

        private Limit $limit;

        private DatabaseDriver $db;

        public function __construct( DatabaseDriver $database = null ) {

            $this->db = $database ?? Database::getConnection();
        }

        public function select( string ... $cols ) {

            $this->select = new Select();
            $this->stmt   = new Statement( $this->select );

            array_map( fn( string $column ) => $this->select->add( new Identifier( $column ) ), $cols );

            return $this;
        }

        public function returning( string ... $cols ) {

            $this->returning = new Returning();
            $this->stmt->add( $this->returning );

            array_map( fn( string $column ) => $this->returning->add( new Identifier( $column ) ), $cols );

            return $this;
        }

        public function insert( $table, $cols ) {

            $this->insert = new Insert( new Identifier( ... $table ) );
            $this->stmt   = new Statement( $this->insert );

            array_map( fn( string $column ) => $this->insert->add( new Identifier( $column ) ), $cols );

            return $this;
        }

        public function values( $data ) {

            $this->values = new Values();
            $this->stmt->add( $this->values );
            array_map( fn( $element ) => $this->values->add( new Value( $element ) ), $data );
            return $this;
        }

        public function from( ?string ... $from ) {
            $this->from = new From( new Identifier( ... $from ) );
            $this->stmt->add( $this->from );
            return $this;
        }

        public function where( array $where ) {

            $expression  = new Expression;
            $this->where = new Where( $expression );
            $this->stmt->add( $this->where );

            array_map( function ( string $column, $value ) use ( $expression ) {

                if ( $expression->fetchLastExpressionItem() instanceof Value ) {
                    $expression->add(
                        new Operator( 'and' )
                    );
                }

                $expression->add( new Identifier( $column ) );
                $expression->add( new Operator( '=' ) );
                $expression->add( new Value( $value ) );
            }, array_keys( $where ), $where );


            return $this;
        }

        public function order( array $order ) {

            $this->order = new Order();
            $this->stmt->add( $this->order );

            array_map( function ( $element ) {

                [$column, $direction] = $element;

                $this->order->add( new Identifier( $column ), $direction );
            }, $order );

            return $this;
        }

        public function limit( int $limit ) {

            $this->limit = new Limit( new Value( $limit ) );
            $this->stmt->add( $this->limit );

            return $this;
        }

        public function fetch() {
            return $res = $this->db->run( $this->stmt )->fetch();
        }

    }
