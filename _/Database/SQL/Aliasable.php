<?php

    namespace Wrapped\_\Database\SQL;

    interface Aliasable {
        public function addAlias ( Expression\Identifier $ident );
    }
