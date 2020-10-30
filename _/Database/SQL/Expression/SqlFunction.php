<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\QueryPart;

    class SqlFunction
    extends QueryPart
    implements ExpressionItem {

        public const SYNTAX = '%s( %s )';

        protected Identifier $name;

        protected array $arguments;

        public function __construct( Identifier $name, ExpressionItem ... $args ) {



            $this->addChild( $name );

            foreach ( $args as $arg ) {
                $this->addChild( $arg );
            }

            $this->name      = $name;
            $this->arguments = $args;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::SYNTAX,
                $this->name->stringify( $driver ),
                implode( ', ', array_map( fn( ExpressionItem $i ) => $i->stringify( $driver ), $this->arguments ) )
            );
        }

    }
