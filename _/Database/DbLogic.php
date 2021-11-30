<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database;

    use \Exception;
    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\GroupBy;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Having;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Limit;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Offset;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Order;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Where;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Bracket;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Conjunction;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\OperatorExpression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    final class DbLogic {

        private ?Limit $limit = null;

        private ?Offset $offset = null;

        private ?GroupBy $groupBy = null;

        private Expression $expression;

        public ?Order $order = null;

        private ?Having $having = null;

        private string $tableName;

        public static function create(): self {
            return new self();
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

        public function where( $column, $op = null, $value = null, $bindToTable = null ): self {

            $this->expression->add( new Identifier( ... [ $bindToTable, $column ] ) );

            // special case for in
            if ( $op !== null && strtolower( $op ) == 'in' ) {
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

        public function isNull(): self {
            $this->expression->add( new Operator( 'is null' ) );

            return $this;
        }

        public function isNotNull() : self{
            $this->expression->add( new Operator( 'is not null' ) );

            return $this;
        }

        public function isTrue(): self {
            $this->expression->add( new Operator( 'is true' ) );

            return $this;
        }

        public function isFalse(): self {
            $this->expression->add( new Operator( 'is false' ) );

            return $this;
        }

        public function isIn( array $param ): self {
            $this->expression->add( new OperatorExpression( "in", ... array_map( fn( $p ) => new Value( $p ), $param ) ) );

            return $this;
        }

        public function isNotIn( array $param ): self {
            $this->expression->add( new OperatorExpression( "not in", ... $param ) );

            return $this;
        }

        public function column( string $column ): self {
            $this->expression->add( new Identifier( $column ) );
            return $this;
        }


        public function op( string $operator ): self {
            $this->expression->add( new Operator( $operator ) );
            return $this;
        }

        public function value( mixed $value ): self {
            $this->expression->add( new Value( $value ) );
            return $this;
        }

        public function openBracket(): self {
            throw new \Exception( 'not supported' );
//            $this->expression->add( new Bracket( "(" ) );
//            return $this;
        }

        public function closeBracket(): self {
            throw new \Exception( 'not supported' );
//            $this->expression->add( new Bracket( ")" ) );
//            return $this;
        }

        public function addOr(): self {
            $this->expression->add( new Conjunction( "or" ) );
            return $this;
        }

        public function addAnd(): self {
            $this->expression->add( new Conjunction( "and" ) );
            return $this;
        }

        public function raw( string $raw ): self {
            throw new \Exception( 'not supported' );
            $this->expression->add( new Raw( $raw ) );
            return $this;
        }

        public function compile( DatabaseDriver $driver ): string {

            $out = ' ' . (new Where( $this->expression ) )->stringify( $driver );

            if ( isset( $this->groupBy ) ) {
                $out .= " {$this->groupBy->stringify( $driver )}";
            }

            if ( isset( $this->order ) ) {
                $out .= " {$this->order->stringify( $driver )}";
            }

            if ( isset( $this->limit ) ) {
                $out .= " {$this->limit->stringify( $driver )}";
            }

            if ( isset( $this->offset ) ) {
                $out .= " {$this->offset->stringify( $driver )}";
            }


            return $out;
        }

        public function limit( string|int $limit ): self {

            $this->limit = new Limit( new Value( $limit ) );
            return $this;
        }

        public function offset( string|int $offset ): self {
            $this->offset = new Offset( new Value( $offset ) );
            return $this;
        }

        public function groupBy( string ... $by ): self {

            $this->groupBy = new GroupBy( new Identifier( ... $by ) );

            return $this;
        }

        public function having( Having $having ): self {

            $this->having = $having;
            return $this;
        }

        public function order( string $column, string $direction = "ASC", string $overrideTable = null, bool $skipQuote = false ): self {

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

        public function getLimit(): ?Limit {
            return $this->limit;
        }

        public function getWhere(): Where {
            return new Where( $this->expression );
        }

        public function getExpression(): Expression {
            return $this->expression;
        }

        public function getGroupBy(): ?GroupBy {
            return $this->groupBy;
        }

        public function getOffset(): ?Offset {
            return $this->offset;
        }

        public function getHaving(): ?Having {
            return $this->having;
        }

        /**
         * checks if current element is AND or OR
         * @return bool
         */
        public function lastItemWasConjunction() {
            return
                $this->expression->fetchLast() === null || $this->expression->fetchLast() instanceof Conjunction;
        }

        public function isEmpty(): bool {
            return $this->expression->fetchLast() === null;
        }

        public function __clone() {
            throw new Exception( 'molly the sheep says no' );
        }

        public function fetchBindings(): array {
            return array_merge(
                $this->expression->fetchBindings(),
                $this->limit?->fetchBindings() ?: [],
                $this->order?->fetchBindings() ?: [],
                $this->offset?->fetchBindings() ?: [],
            );
        }

    }
