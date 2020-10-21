<?php

    namespace Wrapped\_\Database\SQL\Command;

    use \Exception;
    use \Wrapped\_\Database\SQL\ExpressionItem;
    use \Wrapped\_\Database\SQL\Identifier;

    class Update
    implements Command {

        private Identifier $table;

        private array $cols = [];

        private const COMMAND = 'UPDATE %s SET %s';

        public function __construct( Identifier $table ) {
            $this->table = $table;
        }

        public function add( Identifier $column, ExpressionItem $expression ) {

            $this->cols [] = [
                $column,
                $expression
            ];

            return $this;
        }

        public function stringify(): string {

            if ( count( $this->cols ) === 0 ) {
                throw new Exception( "empty update statement" );
            }

            $colParts = [];

            foreach ( $this->cols as [$column, $expression] ) {
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
