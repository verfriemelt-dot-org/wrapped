<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2;

use verfriemelt\wrapped\_\Template\v2\Token\StringToken;
use verfriemelt\wrapped\_\Template\v2\Token\Token;
use verfriemelt\wrapped\_\Template\v2\Token\VariableToken;
use Exception;

final class Tokenizer
{
    private readonly Token $root;

    private int $line = 0;
    private int $offset = 0;
    private int $lineOffset = 0;

    public function __construct()
    {
        $this->root = new Token();
    }

    public function parse(string $input): Token
    {
        while (($token = $this->consume($input)) !== null) {
            $this->root->addChildren($token);
        }

        return $this->root;
    }

    private function consume(string $input): ?Token
    {
        if ($this->offset === \mb_strlen($input)) {
            return null;
        }

        $nextTokenStartPos = \mb_strpos($input, '{{', $this->offset);

        // consume rest of input as stringToken
        if ($nextTokenStartPos === false) {
            return $this->createStringToken(\mb_substr($input, $this->offset));
        }

        $nextTokenEndPos = \mb_strpos($input, '}}', $nextTokenStartPos);

        assert(\is_int($nextTokenEndPos), '$nextTokenEndPos must be int');

        return $this->createToken($input, $nextTokenStartPos, $nextTokenEndPos);
    }

    private function createToken(string $input, int $nextTokenStartPos, int $nextTokenEndPos): Token
    {
        if (!\str_contains($input, '=')) {
            return $this->createVariableToken($input, $nextTokenStartPos, $nextTokenEndPos);
        }

        throw new Exception('not implemented');
    }

    private function createVariableToken(string $input, int $start, int $end): VariableToken
    {
        $expression = trim(\mb_substr($input, $start + 2, $end - $start - 2));

        $token = new VariableToken($this->line, $this->offset, $this->lineOffset);
        $token->setExpression(new Expression($expression));

        $this->offset += $end - $start + 2;

        return $token;
    }

    public function createStringToken(string $input): StringToken
    {
        $stringToken = new StringToken($this->line, $this->offset, $this->lineOffset);
        $stringToken->setContent($input);

        $this->offset += \mb_strlen($input);

        return $stringToken;
    }
}
