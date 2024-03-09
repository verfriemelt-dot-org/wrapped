<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

use RuntimeException;

class Token
{
    /** @var Token[] */
    protected array $children = [];
    protected ?Token $next = null;
    protected ?Token $parent = null;
    protected ?Token $previous = null;

    public function __construct(
        public readonly int $line = 0,
        public readonly int $offset = 0,
        public readonly int $lineOffset = 0,
    ) {}

    private function add(Token $token): void
    {
        $lastKey = \array_key_last($this->children);

        if ($lastKey !== null) {
            $lastToken = $this->children[$lastKey] ?? throw new RuntimeException();
            $lastToken->setNext($token);
            $token->setPrevious($lastToken);
        }

        $this->children[] = $token;
    }

    /**
     * @return Token[]
     */
    public function children(): array
    {
        return $this->children;
    }

    public function parent(): ?Token
    {
        return $this->parent;
    }

    public function next(): ?Token
    {
        return $this->next;
    }

    public function setNext(Token $token): void
    {
        $this->next = $token;
    }

    public function setPrevious(Token $token): void
    {
        $this->previous = $token;
    }

    public function setParent(Token $token): void
    {
        $this->parent = $token;
    }

    public function addChildren(Token ...$token): void
    {
        foreach ($token as $t) {
            $this->add($t);
        }
    }

    public function previous(): ?Token
    {
        return $this->previous;
    }
}
