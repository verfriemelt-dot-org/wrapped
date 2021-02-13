<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Command;

    use \Wrapped\_\Database\SQL\Expression\Bracket;
    use \Wrapped\_\Database\SQL\Expression\ExpressionItem;

    trait CommandWrapperTrait {

        public function wrap( ExpressionItem $item ) {

            if ( $item instanceof CommandExpression ) {
                return (new Bracket() )->add( $item );
            }

            return $item;
        }

    }
