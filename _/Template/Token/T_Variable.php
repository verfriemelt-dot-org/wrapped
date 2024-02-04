<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

use Override;

class T_Variable extends Token
{
    public $formatCallback;

    public bool $escape = true;

    #[Override]
    public function getTokenName(): string
    {
        return 'T_Variable';
    }
}
