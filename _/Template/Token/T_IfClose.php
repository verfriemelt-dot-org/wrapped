<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

    class T_IfClose extends Token
    {
        public bool $negated = false;

        public function getTokenName(): string
        {
            return 'T_IfClose';
        }
    }
