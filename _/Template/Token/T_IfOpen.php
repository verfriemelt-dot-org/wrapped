<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Template\Token;

    class T_IfOpen
    extends Token {

        public $negated = false;

        public function getTokenName() {
            return 'T_IfOpen';
        }

    }
