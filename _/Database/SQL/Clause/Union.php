<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Database\SQL\Clause;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\QueryPart;

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
