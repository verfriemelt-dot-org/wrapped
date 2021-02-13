<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Clause\Clause;
    use \Wrapped\_\Database\SQL\Command\CommandWrapperTrait;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;
    use \Wrapped\_\Database\SQL\QueryPart;

    class Having
    extends QueryPart
    implements Clause {

        use CommandWrapperTrait;

        public const CLAUSE = "HAVING %s";

        private ExpressionItem $expression;

        public function getWeight(): int {
            return 52;
        }

        public function __construct( ExpressionItem $expression ) {

            $wrap = $this->wrap( $expression );

            $this->addChild( $wrap );
            $this->expression = $wrap;
        }

        public function stringify( DatabaseDriver $driver = null ): string {

            return sprintf(
                static::CLAUSE,
                $this->expression->stringify( $driver )
            );
        }

    }
