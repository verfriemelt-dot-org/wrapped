<?php

    namespace Wrapped\_\Database;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Clause\GroupBy;
    use \Wrapped\_\Database\SQL\Clause\Having;
    use \Wrapped\_\Database\SQL\Clause\Limit;
    use \Wrapped\_\Database\SQL\Clause\Offset;
    use \Wrapped\_\Database\SQL\Clause\Order;
    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Expression\Bracket;
    use \Wrapped\_\Database\SQL\Expression\Conjunction;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\OperatorExpression;
    use \Wrapped\_\Database\SQL\Expression\Value;

    class DbLogic {

        private ?Limit $limit = null;

        private ?Offset $offset = null;

        private ?GroupBy $groupBy = null;

        private Expression $expression;

        private ?Order $order = null;

        private ?Having $having = null;

        /**
         *
         * @return \static
         */
        public static function create() {
            return new static();
        }

        public function __construct( $raw = null ) {
            if ( $raw !== null ) {
                throw new Exception( 'raw not supported' );
            }

            $this->expression = new Expression;
        }

        public function setTableName( $table ) {
            $this->tableName = $table;
            return $this;
        }

        public function merge( DbLogic $logic ) {

            throw new Exception( 'merging not supported' );
        }

        public function where( $column, $op = null, $value = null, $bindToTable = null ) {

            $this->expression->add( new Identifier( ... [ $bindToTable, $column ] ) );

            // special case for in
            if ( strtolower( $op ) == 'in' ) {
                return $this->isIn( $value );
            }

            if ( $op ) {
                $this->expression->add( new Operator( $op ) );
            }

            if ( $value !== null ) {
                $this->expression->add( new Value( $value ) );
            }

            return $this;
        }

        public function isNull() {
            $this->expression->add( new Operator( 'is null' ) );

            return $this;
        }

        public function isNotNull() {
            $this->expression->add( new Operator( 'is not null' ) );

            return $this;
        }

        public function isTrue() {
            $this->expression->add( new Operator( 'is true' ) );


            return $this;
        }

        public function isFalse() {
            $this->expression->add( new Operator( 'is false' ) );

            return $this;
        }

        public function isIn( array $param ) {
            $this->expression->add( new OperatorExpression( "in", ... array_map( fn( $p ) => new Value( $p ), $param ) ) );

            return $this;
        }

        public function isNotIn( array $param ) {
            $this->expression->add( new OperatorExpression( "not in", ... $param ) );

            return $this;
        }

        /**
         *
         * @param type $column
         * @return DbLogic
         */
        public function column( $column ) {
            $this->expression->add( new Identifier( $column ) );
            return $this;
        }

        /**
         *
         * @param type $operator
         * @return DbLogic
         */
        public function op( $operator ) {
            $this->expression->add( new Operator( $operator ) );
            return $this;
        }

        /**
         *
         * @param type $value
         * @return DbLogic
         */
        public function value( $value ) {
            $this->expression->add( new Value( $value ) );
            return $this;
        }

        /**
         *
         * @return DbLogic
         */
        public function openBracket() {
            throw new \Exception( 'not supported' );
            $this->expression->add( new Bracket( "(" ) );
            return $this;
        }

        /**
         *
         * @return DbLogic
         */
        public function closeBracket() {
            throw new \Exception( 'not supported' );
            $this->expression->add( new Bracket( ")" ) );
            return $this;
        }

        /**
         *
         * @return DbLogic
         */
        public function addOr() {
            $this->expression->add( new Conjunction( "or" ) );
            return $this;
        }

        /**
         *
         * @return DbLogic
         */
        public function addAnd() {
            $this->expression->add( new Conjunction( "and" ) );
            return $this;
        }

        /**
         *
         * @param type $raw
         * @return DbLogic
         */
        public function raw( $raw ) {
            throw new \Expression( 'not supported' );
            $this->expression->add( new Raw( $raw ) );
            return $this;
        }

        public function compile( DatabaseDriver $driver ): string {

            $out = ' ' . (new Where( $this->expression ) )->stringify( $driver );

            if ( isset( $this->groupBy ) ) {
                $out .= " {$this->groupBy->stringify( $driver )}";
            }

            if ( isset( $this->limit ) ) {
                $out .= " {$this->limit->stringify( $driver )}";
            }

            if ( isset( $this->offset ) ) {
                $out .= " {$this->offset->stringify( $driver )}";
            }

            return $out;
        }

        public function limit( $limit ) {

            $this->limit = new Limit( new Value( $limit ) );
            return $this;
        }

        public function offset( $offset ) {
            $this->offset = new Offset( new Value( $offset ) );
            return $this;
        }

        /**
         *
         * @param type $by
         * @param type $type
         * @return static
         */
        public function groupBy( ... $by ) {

            $this->groupBy = new GroupBy( new Identifier( ... $by ) );

            return $this;
        }

        public function having( Having $having ) {

            $this->having = $having;
            return $this;
        }

        /**
         *
         * @param type $by
         * @param type $type
         * @return static
         */
        public function order( $column, $direction = "ASC", $overrideTable = null, $skipQuote = false ) {

            if ( !$this->order ) {
                $this->order = new Order();
            }

            $this->order->add(
                new Identifier( ... [ $overrideTable, $column ] ),
                $direction
            );

            return $this;
        }

        public function getOrder(): ?Order {
            return $this->order;
        }

        public function getLimit() {
            return $this->limit;
        }

        public function getWhere() {
            return new Where( $this->expression );
        }

        public function getExpression() {
            return $this->expression;
        }

        public function getGroupBy() {
            return $this->groupBy;
        }

        public function getOffset() {
            return $this->offset;
        }

        public function getHaving() {
            return $this->having;
        }

        /**
         * checks if current element is AND or OR
         * @return bool
         */
        public function lastItemWasConjunction() {
            return
                $this->expression->fetchLastExpressionItem() === null || $this->expression->fetchLastExpressionItem() instanceof Conjunction;
        }

        public function isEmpty() {
            return $this->expression->fetchLastExpressionItem() === null;
        }

        public function __clone() {
            throw new Exception( 'molly the sheep says no' );
        }

        public function fetchBindings() {
            return array_merge(
                $this->expression->fetchBindings(),
                $this->limit?->fetchBindings() ?: [],
                $this->order?->fetchBindings() ?: [],
                $this->offset?->fetchBindings() ?: [],
            );
        }

    }
