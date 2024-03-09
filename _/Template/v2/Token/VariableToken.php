<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2\Token;

use verfriemelt\wrapped\_\Template\v2\Expression;

class VariableToken extends Token
{
    private Expression $expression;

    public function setExpression(Expression $expr): void
    {
        $this->expression = $expr;
    }

    public function expression(): Expression
    {
        return $this->expression;
    }
}
