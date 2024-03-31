<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

use verfriemelt\wrapped\_\Template\Expression;

final class ForToken extends Token
{
    public const string MATCH_REGEX = '/for (?<collection>.+?) as (?<value>.+)/';

    private Expression $collectionExpression;
    private RootToken $statements;
    private string $valueName;

    public function setStatements(RootToken $token): void
    {
        $this->statements = $token;
    }

    public function getStatements(): RootToken
    {
        return $this->statements;
    }

    public function setCollectionExpression(Expression $expr): void
    {
        $this->collectionExpression = $expr;
    }

    public function collectionExpression(): Expression
    {
        return $this->collectionExpression;
    }

    public function setValueName(string $name): void
    {
        $this->valueName = $name;
    }

    public function valueName(): string
    {
        return $this->valueName;
    }
}
