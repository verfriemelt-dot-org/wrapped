<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

use verfriemelt\wrapped\_\Template\Expression;

class VariableToken extends Token implements PrintableToken
{
    private Expression $expression;
    private bool $raw = false;
    private string $formatter;

    public function setRaw(bool $raw = false): void
    {
        $this->raw = $raw;
    }

    public function raw(): bool
    {
        return $this->raw;
    }

    public function setExpression(Expression $expr): void
    {
        $this->expression = $expr;
    }

    public function expression(): Expression
    {
        return $this->expression;
    }

    public function hasFormatter(): bool
    {
        return isset($this->formatter);
    }

    public function formatter(): string
    {
        return $this->formatter;
    }

    public function setFormatter(string $formatter): void
    {
        $this->formatter = $formatter;
    }
}
