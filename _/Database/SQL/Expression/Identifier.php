<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;

    class Identifier
    implements ExpressionItem {

        protected string $column;

        protected ?string $table = null;

        protected ?string $schema = null;

        protected ?DatabaseDriver $connection = null;

        public function __construct( string $column, string $table = null, string $schema = null ) {

            if ( strlen( $column ) === 0 ) {
                throw new Exception( 'illegal identifier' );
            }

            $this->column = $column;

            if ( !$table && $schema ) {
                throw new Exception( 'table ident is missing, while schema is present' );
            }

            $this->table  = $table;
            $this->schema = $schema;
        }

        public function setConnection( DatabaseDriver $connection ) {
            $this->connection = $connection;
            return $this;
        }

        public function quote( string $ident ): string {

            if ( !$this->connection ) {
                return $ident;
            }

            return $this->connection->quoteIdentifier( $ident );
        }

        public function stringify(): string {

            $ident = '';

            if ( $this->schema ) {
                $ident .= "{$this->quote( $this->schema )}.";
            }

            if ( $this->table ) {
                $ident .= "{$this->quote( $this->table )}.";
            }

            $ident .= $this->quote( $this->column );

            return $ident;
        }

    }
