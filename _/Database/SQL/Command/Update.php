<?php

    namespace Wrapped\_\Database\SQL\Command;

    use \Exception;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Update
    implements Command {

        use CommandWrapperTrait;

        private Identifier $table;

        private array $columns = [];

        private const COMMAND = 'UPDATE %s SET %s';

        public function __construct( Identifier $table ) {
            $this->table = $table;
        }

        public function add( Identifier $column, ExpressionItem $expression ) {

            $this->columns [] = [
                $column,
                $this->wrap( $expression )
            ];

            return $this;
        }

        public function stringify(): string {

            if ( count( $this->columns ) === 0 ) {
                throw new Exception( "empty update statement" );
            }

            $colParts = [];

            foreach ( $this->columns as [$column, $expression] ) {
                $colParts[] = "{$column->stringify()} = {$expression->stringify()}";
            }

            return sprintf(
                static::COMMAND,
                $this->table->stringify(),
                implode(
                    ", ",
                    $colParts
                )
            );
        }

    }
