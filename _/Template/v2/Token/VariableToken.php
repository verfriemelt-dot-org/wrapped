<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2\Token;

use verfriemelt\wrapped\_\Template\v2\Expression;

class VariableToken extends Token implements PrintableToken
{
    private Expression $expression;
    private bool $raw = false;

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
}
