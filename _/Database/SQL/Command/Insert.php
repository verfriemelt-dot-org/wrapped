<?php

    namespace Wrapped\_\Database\SQL\Command;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Command\Command;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Insert
    extends QueryPart
    implements Command, CommandExpression {

        private const COMMAND = 'INSERT INTO %s ( %s )';

        private array $columns = [];

        private Identifier $into;

        public function __construct( Identifier $ident ) {
            $this->into = $ident;
        }

        public function add( Identifier $column ) {
            $this->columns[] = $column;
            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            if ( count( $this->columns ) === 0 ) {
                throw new Exception( "empty insert into statement" );
            }

            return sprintf(
                static::COMMAND,
                $this->into->stringify(),
                implode(
                    ", ",
                    array_map( fn( ExpressionItem $i ) => $i->stringify(), $this->columns )
                )
            );
        }

    }
