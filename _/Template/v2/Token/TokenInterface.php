<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2\Token;

interface TokenInterface
{
    public function getChildren(): array;

    public function addChildren(TokenInterface $token): static;
}
