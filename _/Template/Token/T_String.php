<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Template\Token;

    class T_String
    extends Token {

        public function getTokenName() {
            return 'T_String';
        }

        public function matches( $void ) {
            return true;
        }

    }
