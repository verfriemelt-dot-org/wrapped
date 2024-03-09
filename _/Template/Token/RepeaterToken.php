<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

class RepeaterToken extends Token
{
    private string $name;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }
}
