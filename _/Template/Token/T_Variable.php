<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

class T_Variable extends Token
{
    public $formatCallback;

    public bool $escape = true;

    public function getTokenName(): string
    {
        return 'T_Variable';
    }
}
