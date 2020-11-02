<?php

    namespace Wrapped\_\Database\Facade;

    use \Exception;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Clause\From;
    use \Wrapped\_\Database\SQL\Clause\GroupBy;
    use \Wrapped\_\Database\SQL\Clause\Limit;
    use \Wrapped\_\Database\SQL\Clause\Offset;
    use \Wrapped\_\Database\SQL\Clause\Order;
    use \Wrapped\_\Database\SQL\Clause\Returning;
    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Command\Delete;
    use \Wrapped\_\Database\SQL\Command\Insert;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Command\Update;
    use \Wrapped\_\Database\SQL\Command\Values;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\OperatorExpression;
    use \Wrapped\_\Database\SQL\Expression\Primitive;
    use \Wrapped\_\Database\SQL\Expression\SqlFunction;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\Database\SQL\Statement;
    use \Wrapped\_\DataModel\PropertyObjectInterface;

    class QueryBuilder {

        public Statement $stmt;

        public Select $select;

        public Insert $insert;

        public Update $update;

        public Delete $delete;

        public Values $values;

        public From $from;

        public Where $where;

        public Returning $returning;

        public Order $order;

        public Limit $limit;

        public Offset $offset;

        public GroupBy $groupBy;

        public ?DatabaseDriver $db = null;

        public function __construct( DatabaseDriver $database = null ) {
            $this->db   = $database;
            $this->stmt = new Statement();
        }

        protected function boxIdent( $ident ): array {
            return !is_array( $ident ) ? [ $ident ] : $ident;
        }

        public function fetchStatement(): Statement {
            return $this->stmt;
        }

        public function select( ... $cols ) {
            $this->select = new Select();
            $this->stmt->setCommand( $this->select );


            array_map( function( $column ) {
                if ( is_array( $column ) ) {
                    $this->select->add( new Identifier( ... $column ) );
                } else {
                    $this->select->add( new Identifier( $column ) );
                }
            }, $cols );

            return $this;
        }

        public function count( $table, $what = '*', bool $district = false ) {

            $this->select = new Select();

            if ( $district ) {
                $this->select->add( new SqlFunction( new Identifier( 'count' ), new Expression( new Operator( 'distinct' ), new Identifier( $what ) ) ) );
            } else {
                $this->select->add( new SqlFunction( new Identifier( 'count' ), new Identifier( $what ) ) );
            }

            $this->stmt = new Statement( $this->select );

            $this->from( ... $this->boxIdent( $table ) );

            return $this;
        }

        public function delete( $table ) {

            $this->delete = new Delete( new Identifier( ...  $this->boxIdent($table) ) );
            $this->stmt->setCommand( $this->delete );

            return $this;
        }

        public function update( $table, array $cols ) {

            $this->update = new Update( new Identifier( ...  $this->boxIdent($table) ) );
            $this->stmt->setCommand( $this->update );

            array_map( fn( string $column, $value ) => $this->update->add( new Identifier( $column ), new Value( $value ) ), array_keys( $cols ), $cols );

            return $this;
        }

        public function insert( $table, $cols ) {

            $this->insert = new Insert( new Identifier( ...  $this->boxIdent($table) ) );
            $this->stmt->setCommand( $this->insert );

            array_map( fn( string $column ) => $this->insert->add( new Identifier( $column ) ), $cols );

            return $this;
        }

        public function returning( string ... $cols ) {

            $this->returning = new Returning();
            $this->stmt->add( $this->returning );

            array_map( fn( string $column ) => $this->returning->add( new Identifier( $column ) ), $cols );

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

                if ( $expression->fetchLastExpressionItem() !== null && !($expression->fetchLastExpressionItem() instanceof Operator) ) {
                    $expression->add(
                        new Operator( 'and' )
                    );
                }

                if ( is_array( $value ) ) {

                    $expression->add( new Identifier( $column ) );

                    $op = new OperatorExpression( 'in', ... array_map( fn( string $value ) => new Value( $value ), $value ) );
                    $expression->add( $op );
                } else {

                    $expression->add( new Identifier( $column ) );

                    if ( $value instanceof PropertyObjectInterface ) {
                        $value = $value->dehydrateToString();
                    }

                    if ( in_array( $value, [ false, true, null ], true ) ) {
                        $expression->add( new Operator( 'is' ) );
                        $expression->add( new Primitive( $value ) );
                    } else {
                        $expression->add( new Operator( '=' ) );
                        $expression->add( new Value( $value ) );
                    }
                }
            }, array_keys( $where ), $where );


            return $this;
        }

        public function order( array $order ) {

            $this->order = new Order();
            $this->stmt->add( $this->order );

            array_map( function ( $element ) {

                [$column, $direction] = $element;

                $this->order->add( new Identifier( $column ), $direction ?? 'ASC'  );
            }, $order );

            return $this;
        }

        public function groupBy( array $f ) {
            $this->groupBy = new GroupBy( new Identifier( ... $f ) );
            $this->stmt->add( $this->groupBy );
            return $this;
        }

        public function limit( int $limit ) {

            $this->limit = new Limit( new Value( $limit ) );
            $this->stmt->add( $this->limit );

            return $this;
        }

        public function offset( int $offset ) {

            $this->offset = new Offset( new Value( $offset ) );
            $this->stmt->add( $this->offset );

            return $this;
        }

        public function fetchAll(): array {
            return $this->run()->fetchAll();
        }

        public function fetch(): array {
            return $this->run()->fetch();
        }

        public function run() {

            if ( !$this->db ) {
                throw new Exception( 'cannot run query without a databaseconnection' );
            }

            return $this->db->run( $this->stmt );
        }

        public function stringify(): string {
            return $this->stmt->stringify();
        }

        /**
         *
         * @param DbLogic $logic
         * @return static
         * @deprecated since version number
         */
        public function translateDbLogic( DbLogic $logic ): static {

            if ( $logic->getLimit() ) {
                $this->limit = $logic->getLimit();
                $this->stmt->add( $this->limit );
            }

            if ( $logic->getOffset() ) {
                $this->offset = $logic->getOffset();
                $this->stmt->add( $this->offset );
            }

            if ( $logic->getWhere() ) {
                $this->where = $logic->getWhere();
                $this->stmt->add( $this->where );
            }

            if ( $logic->getOrder() ) {
                $this->order = $logic->getOrder();
                $this->stmt->add( $this->order );
            }

            return $this;
        }

    }
