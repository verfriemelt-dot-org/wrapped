<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

class StringToken extends Token implements PrintableToken
{
    private string $content = '';

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function content(): string
    {
        return $this->content;
    }
}
