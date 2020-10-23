<?php

    namespace Wrapped\_\Database\SQL\Expression;

    use \TheSeer\Tokenizer\Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\QueryPart;

    class OperatorExpression
    extends QueryPart
    implements ExpressionItem {

        public const OPTERATORS = [
            'exists'  => [
                "minArgs" => 1,
                "maxArgs" => 1,
                "string"  => 'EXISTS ( %s )'
            ],
            'in'      => [
                "minArgs" => 1,
                "maxArgs" => \INF,
                "string"  => 'IN ( %s )',
            ],
            'not in'      => [
                "minArgs" => 1,
                "maxArgs" => \INF,
                "string"  => 'NOT IN ( %s )',
            ],
            'between' => [
                "minArgs" => 2,
                "maxArgs" => 2,
                "string"  => 'BETWEEN ( %s ) AND ( %s )'
            ]
        ];

        protected string $operator;

        protected array $arguments;

        public function __construct( string $op, ExpressionItem ... $args ) {

            $op = strtolower( $op );

            if ( !isset( static::OPTERATORS[$op] ) ) {
                throw new Exception( 'illegal operator' );
            }

            if ( count( $args ) < static::OPTERATORS[$op]['minArgs'] || count( $args ) > static::OPTERATORS[$op]['maxArgs'] ) {
                throw new Exception( 'missing arguments for operator' );
            }

            foreach ( $args as $arg ) {
                $this->addChild( $arg );
            }

            $this->operator  = $op;
            $this->arguments = $args;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            // somewhat hacky for IN operator

            switch ( $this->operator ) {
                case 'in':
                    return sprintf(
                        static::OPTERATORS[$this->operator]['string'],
                        implode( ', ', array_map( fn( ExpressionItem $i ) => $i->stringify( $driver ), $this->arguments ) )
                    );
            }

            return sprintf(
                static::OPTERATORS[$this->operator]['string'],
                ... array_map( fn( ExpressionItem $i ) => $i->stringify( $driver ), $this->arguments )
            );
        }

    }