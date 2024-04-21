<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

final class IncludeToken extends Token
{
    public const string MATCH_REGEX = '/include (?<path>.+)/';

    private string $path;

    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
