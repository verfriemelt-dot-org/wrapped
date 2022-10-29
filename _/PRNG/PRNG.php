<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\PRNG;

    use Exception;

    class PRNG
    {
//     Xn+1 = (aXn + c) mod m
//    where X is the sequence of pseudo-random values
//    m, 0 < m - modulus
//    a, 0 < a < m - multiplier
//    c, 0 ≤ c < m - increment
//    x0, 0 ≤ x0 < m - the seed or start value

        protected int $modulus = 2 ** 31 - 1;

        protected int $multiplier = 48271;

        protected int $increment = 0;

        protected int $seed;

        protected float $last;

        public function __construct(int $seed = 1103515245)
        {
            if ($seed < 0) {
                throw new Exception('seed must be greater than zero');
            }

            $this->seed = $seed;
            $this->last = $seed;
        }

        public function getSeed(): int
        {
            return $this->seed;
        }

        public function next(): float
        {
            return $this->last = ($this->multiplier * $this->last + $this->increment) % $this->modulus;
        }
    }
