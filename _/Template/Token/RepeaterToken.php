<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

class RepeaterToken extends Token implements LegacyToken
{
    private string $name;

    public const string MATCH_STRING = 'repeater=';

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }
}
