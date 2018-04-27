<?php

    namespace Wrapped\_\Database;

    use \Wrapped\_\Database\SQL\Logic\Bracket;
    use \Wrapped\_\Database\SQL\Logic\Column;
    use \Wrapped\_\Database\SQL\Logic\Conjunction;
    use \Wrapped\_\Database\SQL\Logic\LogicItem;
    use \Wrapped\_\Database\SQL\Logic\NullValue;
    use \Wrapped\_\Database\SQL\Logic\Operator;
    use \Wrapped\_\Database\SQL\Logic\Raw;
    use \Wrapped\_\Database\SQL\Logic\Value;
    use \Wrapped\_\Database\SQL\Order;

    class DbLogic {

        /** @var LogicItem * */
        public $logicChainStart;

        /** @var LogicItem * */
        public $logicChainCurrent;
        public $orderby          = [];
        private $bindings        = [
            "params" => [],
            "vars"   => []
        ];
        private $rawString;
        private $tableName;
        private $bindingsCounter = 0;
        private $limit;
        private $offset;
        private $groupBy;
        private $having;

        /**
         *
         * @return \static
         */
        public static function create() {
            return new static();
        }

        public function __construct( $raw = null ) {
            $this->rawString = $raw;
        }

        public function getBindingsCounter() {
            return $this->bindingsCounter;
        }

        public function setBindingsCounter( $count ) {
            $this->bindingsCounter = $count;
            return $this;
        }

        public function setTableName( $table ) {
            $this->tableName = $table;
            return $this;
        }

        private function appendToChain( LogicItem $item ) {

            if ( $this->tableName !== null ) {
                $item->setTableName( $this->tableName );
            }

            if ( $this->logicChainStart === null ) {
                $this->logicChainStart   = $item;
                $this->logicChainCurrent = $item;
            } else {
                $this->logicChainCurrent->setNext( $item );
            }

            $this->logicChainCurrent = $item;
        }

        public function merge( DbLogic $logic ) {

            if ( $this->logicChainCurrent === null ) {

                $this->logicChainStart   = $logic->logicChainStart;
                $this->logicChainCurrent = $logic->logicChainCurrent;
            } else {

                $this->addAnd();
                $this->logicChainCurrent->setNext( $logic->logicChainStart );
            }

            foreach ( $logic->getOrderBy() as $order ) {
                $this->orderby[] = $order;
            }

            $this->logicChainCurrent = $logic->logicChainCurrent;
            return $this;
        }

        /**
         *
         * @param type $column
         * @param type $op
         * @param type $value
         * @return DbLogic
         */
        public function where( $column, $op = null, $value = null, $bindToTable = null ) {

            $logicColumn = new Column( $column );

            if ( $bindToTable !== null )
                $logicColumn->setTableName( $bindToTable );

            $this->appendToChain( $logicColumn );

            $op !== null && $this->appendToChain( new Operator( $op ) );
            $value !== null && $this->appendToChain( new Value( $value ) );

            return $this;
        }

        public function isNull() {
            $this->appendToChain( new Operator( "IS" ) );
            $this->appendToChain( new NullValue() );

            return $this;
        }

        public function isNotNull() {
            $this->appendToChain( new Operator( "IS NOT" ) );
            $this->appendToChain( new NullValue() );

            return $this;
        }

        public function isIn( array $param ) {
            $this->appendToChain( new Operator( "IN" ) );
            $this->appendToChain( new Value( $param ) );

            return $this;
        }

        public function isNotIn( array $param ) {
            $this->appendToChain( new Operator( "NOT IN" ) );
            $this->appendToChain( new Value( $param ) );

            return $this;
        }

        /**
         *
         * @param type $column
         * @return DbLogic
         */
        public function column( $column ) {
            $this->appendToChain( $column );

            return $this;
        }

        /**
         *
         * @param type $operator
         * @return DbLogic
         */
        public function op( $operator ) {
            $this->appendToChain( new Operator( $operator ) );
            return $this;
        }

        /**
         *
         * @param type $value
         * @return DbLogic
         */
        public function value( $value ) {
            $this->appendToChain( new Value( $value ) );
            return $this;
        }

        /**
         *
         * @return DbLogic
         */
        public function openBracket() {
            $this->appendToChain( new Bracket( "(" ) );
            return $this;
        }

        /**
         *
         * @return DbLogic
         */
        public function closeBracket() {
            $this->appendToChain( new Bracket( ")" ) );
            return $this;
        }

        /**
         *
         * @return DbLogic
         */
        public function addOr() {
            $this->appendToChain( new Conjunction( "or" ) );
            return $this;
        }

        /**
         *
         * @return DbLogic
         */
        public function addAnd() {
            $this->appendToChain( new Conjunction( "and" ) );
            return $this;
        }

        /**
         *
         * @param type $raw
         * @return DbLogic
         */
        public function raw( $raw ) {
            $this->appendToChain( new Raw( $raw ) );
            return $this;
        }

        private function parseLogicChain() {

            $currentLogicItem = $this->logicChainStart;
            $string           = "";

            do {
                $string .= " " . $currentLogicItem->fetchSqlString( $this );
            } while ( $currentLogicItem = $currentLogicItem->getNext() );

            return $string;
        }

        private function compile( $withoutWhereBlock = false ) {

            $string = "";

            if ( $this->logicChainStart !== null ) {
                $string .= (!$withoutWhereBlock) ? " WHERE" : "";
                $string .= $this->parseLogicChain();
            }

            if ( $this->groupBy !== null ) {
                $string .= " " . $this->groupBy;
            }

            if ( $this->having !== null ) {
                $string .= " HAVING " . $this->having;
            }


            if ( !empty( $this->orderby ) ) {

                $orders = array_map( function ( Order $order ) {
                    return $order->fetchOrderString();
                }
                    , $this->orderby
                );

                $string .= " ORDER BY " . implode( ",", $orders );
            }

            if ( $this->limit !== null ) {
                $string .= " LIMIT ";
                $string .= ($this->offset !== null) ? $this->offset . "," : "";
                $string .= $this->limit;
            }

            return $string;
        }

        /**
         *
         * @return []
         */
        public function getBindings() {
            return $this->bindings;
        }

        /**
         *
         * @return string
         */
        public function getString( $withoutWhereBlock = false ) {
            return $this->rawString ?: $this->compile( $withoutWhereBlock );
        }

        /**
         *
         * @param type $limit
         * @return DbLogic
         */
        public function limit( $limit ) {
            $this->limit = $limit;
            return $this;
        }

        /**
         * @param type $offset
         * @return static
         */
        public function offset( $offset ) {
            $this->offset = $offset;
            return $this;
        }

        /**
         *
         * @param type $by
         * @param type $type
         * @return static
         */
        public function groupBy( $by ) {
            $this->groupBy = " GROUP BY {$by} ";
            return $this;
        }

        public function having( $having ) {
            $this->having = $having;
            return $this;
        }

        /**
         *
         * @param type $by
         * @param type $type
         * @return static
         */
        public function order( $column, $direction = "ASC", $overrideTable = null ) {
            $this->orderby[] = new Order( $column, $direction, $overrideTable ?: $this->getTableName() );
            return $this;
        }

        /**
         * binds the given value to the query
         * @param type $value
         * @return DbLogic
         */
        public function bindValue( $value ) {

            $binding                    = "bind" . $this->bindingsCounter++;
            $this->bindings["params"][] = $binding;
            $this->bindings["vars"][]   = $value;

            return $binding;
        }

        /**
         * returns the current tablename selected
         * @return type
         */
        public function getTableName() {
            return $this->tableName;
        }

        /**
         * transforms arrays to logic
         * @param type $array
         */
        public function parseArray( $data ) {

            $count = count( $data );
            $i     = 0;

            foreach ( $data as $column => $value ) {

                ++$i;

                if ( is_array( $value ) ) {
                    $this->where( $column, "IN", $value );
                } else {
                    $this->where( $column, "=", $value );
                }

                if ( $i + 1 <= $count ) {
                    $this->addAnd();
                }
            }
        }

        /**
         *
         * @return Order[]
         */
        public function getOrderBy() {
            return $this->orderby;
        }

        /**
         * checks if current element is AND or OR
         * @return bool
         */
        public function lastItemWasConjunction() {
            return $this->logicChainCurrent instanceof Conjunction;
        }

        public function isEmpty() {
            return $this->logicChainCurrent === null;
        }

        public function __clone() {

            if ( $this->logicChainStart === null ) {
                return;
            }

            $current = $this->logicChainStart;

            $this->logicChainCurrent = null;
            $this->logicChainStart   = null;

            $this->appendToChain( clone $current );

            while ( $next = $current->getNext() ) {
                $clone   = clone $next;
                $this->appendToChain( $clone );
                $current = $clone;
            }
        }

    }
