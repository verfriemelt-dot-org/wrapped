<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

class T_IfOpen extends Token
{
    public bool $negated = false;

    public function getTokenName(): string
    {
        return 'T_IfOpen';
    }
}
