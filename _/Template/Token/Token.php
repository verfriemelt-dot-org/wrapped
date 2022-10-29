<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

    abstract class Token
    {
        public string $currentContent = '';

        public int $currentLength = 0;

        public int $priority = 10;

        public int $maxLength = 0;

        public ?Token $nextToken = null;

        public ?Token $prevToken = null;

        private int $line;

        private int $linePos;

        private int $pos;

        abstract public function getTokenName(): string;

        public function setPrevToken(Token $prev): static
        {
            $this->prevToken = $prev;
            $this->prevToken->nextToken = $this;
            return $this;
        }

        public function setNextToken(Token $next = null): static
        {
            $this->nextToken = $next;
            $this->nextToken->prevToken = $this;
            return $this;
        }

        public function getContent(): string
        {
            return $this->currentContent;
        }

        public function getLine(): int
        {
            return $this->line;
        }

        public function getLinePos(): int
        {
            return $this->linePos;
        }

        public function getPos(): int
        {
            return $this->pos;
        }

        public function setLine(int $line): static
        {
            $this->line = $line;
            return $this;
        }

        public function setLinePos(int $linePos): static
        {
            $this->linePos = $linePos;
            return $this;
        }

        public function setPos(int $pos): static
        {
            $this->pos = $pos;
            return $this;
        }
    }
