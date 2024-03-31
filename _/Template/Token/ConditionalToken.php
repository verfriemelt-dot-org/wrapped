<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

use verfriemelt\wrapped\_\Template\Expression;

class ConditionalToken extends Token
{
    private Expression $expression;

    private RootToken $consequent;
    private RootToken $alternative;

    public const string MATCH_STRING = 'if=';

    public function setExpression(Expression $expr): void
    {
        $this->expression = $expr;
    }

    public function expression(): Expression
    {
        return $this->expression;
    }

    public function setConsequent(RootToken $token): void
    {
        $this->consequent = $token;
    }

    public function setAlternative(RootToken $token): void
    {
        $this->alternative = $token;
    }

    public function consequent(): RootToken
    {
        return $this->consequent;
    }

    public function alternative(): RootToken
    {
        return $this->alternative;
    }

    public function hasAlternative(): bool
    {
        return isset($this->alternative);
    }
}
