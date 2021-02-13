<?php

    declare(strict_types = 1);

    namespace Wrapped\_\PRNG;

    class PRNG {

//     Xn+1 = (aXn + c) mod m
//    where X is the sequence of pseudo-random values
//    m, 0 < m - modulus
//    a, 0 < a < m - multiplier
//    c, 0 ≤ c < m - increment
//    x0, 0 ≤ x0 < m - the seed or start value

        static protected int $modulus = 2 ** 31 - 1;

        static protected int $multiplier = 48271;

        static protected int $increment = 0;

        static protected int $seed = 1103515245;

        static protected int $last = 1103515245;

        public static function setSeed( int $seed ): void {

            if ( $seed < 0 ) {
                throw new Exception( 'seed must be greater than zero' );
            }

            static::$seed = $seed;
        }

        public static function getSeed(): int {
            return static::$seed;
        }

        public static function next(): int {
            return static::$last = ( static::$multiplier * static::$last + self::$increment ) % static::$modulus;
        }

    }
