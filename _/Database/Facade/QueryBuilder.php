<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\Facade;

    use \Exception;
    use \verfriemelt\wrapped\_\Database\DbLogic;
    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\From;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\GroupBy;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Having;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Limit;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Offset;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Order;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Returning;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Where;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Delete;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Insert;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Select;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Update;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Values;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Conjunction;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\OperatorExpression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\SqlFunction;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;
    use \verfriemelt\wrapped\_\Database\SQL\Statement;
    use \verfriemelt\wrapped\_\DataModel\PropertyObjectInterface;

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

        public Having $having;

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

            array_map( function ( $column ) {
                if ( is_array( $column ) ) {
                    $this->select->add( new Identifier( ... $column ) );
                } elseif ( $column instanceof \verfriemelt\wrapped\_\Database\SQL\QueryPart ) {
                    $this->select->add( $column );
                } else {
                    $this->select->add( new Identifier( $column ) );
                }
            }, $cols );

            return $this;
        }

        public function count( $table, $what = '*', bool $distinct = false ) {

            $this->select = new Select();

            if ( $what !== '*' ) {
                $what = [ ... ( is_array( $table ) ? $table : [ $table ]), $what ];
            } else {
                $what = [ $what ];
            }

            if ( $distinct ) {
                $this->select->add( (new SqlFunction( new Identifier( 'count' ), new Expression( new Operator( 'distinct' ), new Identifier( ... $what ) ) ))->as( new Identifier('count') ) );
            } else {
                $this->select->add( (new SqlFunction( new Identifier( 'count' ), new Identifier( ... $what ) ))->as( new Identifier('count') ) );
            }

            $this->stmt = new Statement( $this->select );

            $this->from( ... $this->boxIdent( $table ) );

            return $this;
        }

        public function delete( $table ) {

            $this->delete = new Delete( new Identifier( ... $this->boxIdent( $table ) ) );
            $this->stmt->setCommand( $this->delete );

            return $this;
        }

        public function update( $table, array $cols ) {

            $this->update = new Update( new Identifier( ... $this->boxIdent( $table ) ) );
            $this->stmt->setCommand( $this->update );

            array_map( fn( string $column, $value ) => $this->update->add( new Identifier( $column ), new Value( $value ) ), array_keys( $cols ), $cols );

            return $this;
        }

        public function insert( $table, $cols ) {

            $this->insert = new Insert( new Identifier( ... $this->boxIdent( $table ) ) );
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

        protected function getWhereExpression(): Expression {

            if ( isset( $this->where ) ) {
                return $this->where->expression;
            }

            $this->where = new Where( new Expression );
            $this->stmt->add( $this->where );

            return $this->where->expression;
        }

        public function where( array $where ) {

            $expression = $this->getWhereExpression();

            array_map( function ( $column, $value ) use ( $expression ) {

                if ( $expression->fetchLast() !== null ) {
                    $expression->add(
                        new Conjunction( 'and' )
                    );
                }

                // special handling for where parameters in the style of [ 'col', 'op', 'value' ];
                // is_integer( $column ) checks if we have numeric keys
                if ( is_integer( $column ) && count( $value ) === 3 ) {

                    $expression->add( new Identifier( ... explode( '.', $value[0] ) ) );

                    if ( is_array( $value[2] ) ) {
                        $op = new OperatorExpression( 'in', ... array_map( fn( $value ) => new Value( $value ), $value[2] ) );
                        $expression->add( $op );
                    } elseif ( in_array( $value[2], [ false, true, null ], true ) ) {
                        // cast to operator directly IS TRUE, IS FALSE, IS NULL
                        $expression->add( new Operator( 'is ' . (new Value( $value[2] ) )->stringify() ) );
                    }
                    else {
                        $expression->add( new Operator( $value[1] ) );
                        $expression->add( new Value( $value[2] ) );
                    }

                    return;
                }

                $expression->add( new Identifier( ... explode( '.', $column ) ) );

                if ( is_array( $value ) ) {

                    $op = new OperatorExpression( 'in', ... array_map( fn( $value ) => new Value( $value ), $value ) );
                    $expression->add( $op );
                } else {

                    if ( $value instanceof PropertyObjectInterface ) {
                        $value = $value->dehydrateToString();
                    }

                    if ( in_array( $value, [ false, true, null ], true ) ) {
                        // cast to operator directly IS TRUE, IS FALSE, IS NULL
                        $expression->add( new Operator( 'is ' . (new Value( $value ) )->stringify() ) );
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
            return $this->run()->fetchAll() ?: [];
        }

        public function fetch(): ?array {
            return $this->run()->fetch() ?: null;
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

            if ( null !== $logic->getLimit() ) {
                $this->limit = $logic->getLimit();
                $this->stmt->add( $this->limit );
            }

            if ( null !==$logic->getOffset() ) {
                $this->offset = $logic->getOffset();
                $this->stmt->add( $this->offset );
            }

            $this->where = $logic->getWhere();
            $this->stmt->add( $this->where );

            if ( null !== $logic->getOrder() ) {
                $this->order = $logic->getOrder();
                $this->stmt->add( $this->order );
            }

            if ( null !==$logic->getGroupBy() ) {
                $this->groupBy = $logic->getGroupBy();
                $this->stmt->add( $this->groupBy );
            }

            if ( null !==$logic->getHaving() ) {
                $this->having = $logic->getHaving();
                $this->stmt->add( $this->having );
            }

            return $this;
        }

    }
