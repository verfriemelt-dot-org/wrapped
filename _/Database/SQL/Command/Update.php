<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL\Command;

    use \Exception;
    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\QueryPart;

    class Update
    extends QueryPart
    implements Command {

        use CommandWrapperTrait;

        private Identifier $table;

        private array $columns = [];

        private const COMMAND = 'UPDATE %s SET %s';

        public function __construct( Identifier $table ) {
            $this->table = $table;
        }

        public function getWeight(): int {
            return 10;
        }

        public function add( Identifier $column, QueryPart $expression ) {

            $wrappedExpression = $this->wrap( $expression );
            $this->addChild( $wrappedExpression );
            $this->addChild( $column );

            $this->columns [] = [
                $column,
                $wrappedExpression
            ];

            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            if ( count( $this->columns ) === 0 ) {
                throw new Exception( "empty update statement" );
            }

            $colParts = [];

            foreach ( $this->columns as [$column, $expression] ) {
                $colParts[] = "{$column->stringify( $driver )} = {$expression->stringify( $driver )}";
            }

            return sprintf(
                static::COMMAND,
                $this->table->stringify( $driver ),
                implode(
                    ", ",
                    $colParts
                )
            );
        }

    }
