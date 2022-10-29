<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL;

    interface Aliasable
    {
        public function addAlias(Expression\Identifier $ident);
    }
