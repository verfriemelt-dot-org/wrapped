<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2\Token;

abstract class AbstractToken implements TokenInterface
{
    /** @var list<TokenInterface> */
    private array $children = [];

    /** @return list<TokenInterface> */
    final public function getChildren(): array
    {
        return $this->children;
    }

    final public function addChildren(TokenInterface $token): static
    {
        $this->children[] = $token;
        return $this;
    }
}
