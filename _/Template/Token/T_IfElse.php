<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

use Override;

class T_IfElse extends Token
{
    public bool $negated = false;

    #[Override]
    public function getTokenName(): string
    {
        return 'T_IfElse';
    }
}
