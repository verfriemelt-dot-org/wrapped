<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Template\Token;

    class T_String
    extends Token {

        public function getTokenName(): string {
            return 'T_String';
        }
    }
