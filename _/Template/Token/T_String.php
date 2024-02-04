<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

use Override;

class T_String extends Token
{
    #[Override]
    public function getTokenName(): string
    {
        return 'T_String';
    }
}
