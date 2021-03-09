<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL\Command;

    use \verfriemelt\wrapped\_\Database\SQL\Expression\Bracket;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\ExpressionItem;

    trait CommandWrapperTrait {

        public function wrap( ExpressionItem $item ) {

            if ( $item instanceof CommandExpression ) {
                return (new Bracket() )->add( $item );
            }

            return $item;
        }

    }
