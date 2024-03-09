<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2\Token;

use verfriemelt\wrapped\_\Template\v2\Expression;

class ConditionalToken extends Token
{
    private Expression $expression;

    private Token $consequent;
    private Token $alternative;

    public function setExpression(Expression $expr): void
    {
        $this->expression = $expr;
    }

    public function expression(): Expression
    {
        return $this->expression;
    }

    public function setConsequent(Token $token): void
    {
        $this->consequent = $token;
    }

    public function setAlternative(Token $token): void
    {
        $this->alternative = $token;
    }

    public function Consequent(): Token
    {
        return $this->consequent;
    }

    public function Alternative(): Token
    {
        return $this->alternative;
    }

    public function hasAlternative(): bool
    {
        return isset($this->alternative);
    }
}
