<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Template\Token;

    class T_Variable
    extends Token {

        public $formatCallback;

        public $escape = true;

        public function getTokenName() {
            return 'T_Variable';
        }

    }
