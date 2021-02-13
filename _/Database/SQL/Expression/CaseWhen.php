<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Expression;

    use \Exception;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Alias;
    use \Wrapped\_\Database\SQL\QueryPart;

    class CaseWhen
    extends QueryPart
    implements ExpressionItem {

        use Alias;

        public const SYNTAX = 'CASE %s END';

        protected Identifier $name;

        protected ?ExpressionItem $expression = null;

        protected array $when = [];

        protected ?ExpressionItem $else = null;

        public function __construct( ?ExpressionItem $exp = null ) {

            if ( $exp !== null ) {
                $this->addChild( $exp );
                $this->expression = $exp;
            }
        }

        public function when( ExpressionItem $when, ExpressionItem $then ): static {

            $this->addChild( $when );
            $this->addChild( $then );

            $this->when[] = [ "when" => $when, "then" => $then ];

            return $this;
        }

        public function else( ExpressionItem $else ): static {

            $this->addChild( $else );
            $this->else = $else;

            return $this;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            $exp = ( $this->expression ) ? "{$this->expression->stringify( $driver )} " : "";

            if ( empty( $this->when ) ) {
                throw new Exception( 'when cannot be empty' );
            }

            $when = implode( ' ',
                array_map( function ( $part ) use ( $driver ) {
                    return "WHEN {$part['when']->stringify( $driver )} THEN {$part['then']->stringify( $driver )}";
                },
                    $this->when
                )
            );

            $else = ( $this->else !== null ) ? " ELSE {$this->else->stringify( $driver )}" : "";

            return sprintf(
                    static::SYNTAX,
                    $exp . $when . $else
                )
                . $this->stringifyAlias( $driver );
        }

    }
