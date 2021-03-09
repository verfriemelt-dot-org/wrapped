<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Database\SQL\Clause;

    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\SQL\QueryPart;

    class Union
    extends QueryPart
    implements Clause {

        public const CLAUSE = "UNION ALL";

        public function getWeight(): int {
            return 10000;
        }

        public function stringify( DatabaseDriver $driver = null ): string {
            return "UNION ALL";
        }

    }
