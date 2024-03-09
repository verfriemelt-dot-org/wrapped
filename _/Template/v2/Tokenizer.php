<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2;

use verfriemelt\wrapped\_\Template\v2\Token\RepeaterToken;
use verfriemelt\wrapped\_\Template\v2\Token\StringToken;
use verfriemelt\wrapped\_\Template\v2\Token\Token;
use verfriemelt\wrapped\_\Template\v2\Token\VariableToken;
use Exception;
use RuntimeException;

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

        if ($nextTokenStartPos > $this->offset) {
            return $this->createStringToken(\mb_substr($input, $this->offset, $nextTokenStartPos - $this->offset));
        }

        $nextTokenEndPos = \mb_strpos($input, '}}', $nextTokenStartPos);

        assert(\is_int($nextTokenEndPos), '$nextTokenEndPos must be int');

        return $this->createToken($input, $nextTokenStartPos, $nextTokenEndPos);
    }

    private function createToken(string $input, int $nextTokenStartPos, int $nextTokenEndPos): Token
    {
        $tokenContent = \mb_substr($input, $nextTokenStartPos, $nextTokenEndPos);

        if (!\str_contains($tokenContent, '=')) {
            return $this->createVariableToken($input, $nextTokenStartPos, $nextTokenEndPos);
        }

        if (\str_contains($tokenContent, 'repeater=')) {
            return $this->createRepeaterToken($input, $nextTokenStartPos, $nextTokenEndPos);
        }

        throw new Exception('not implemented');
    }

    private function createRepeaterToken(string $input, int $start, int $end): RepeaterToken
    {
        $matches = [];
        $expression = \trim(\mb_substr($input, $start + 2, $end - $start - 2));

        if (preg_match("/.*?(?<closing>\/)?repeater='(?<name>.*?)'.*?/", $expression, $matches) !== 1) {
            throw new RuntimeException('cannot match repeater token: ' . $expression);
        }

        assert(is_string($matches['name'] ?? null), 'match name missing');

        $token = new RepeaterToken($this->line, $this->offset, $this->lineOffset);
        $token->setName($matches['name']);

        $this->offset += $end - $start + 2;

        if (($matches['closing'] ?? null) === '/') {
            return $token;
        }

        do {
            $innerToken = $this->consume($input);

            if ($innerToken instanceof RepeaterToken && $innerToken->name() === $token->name()) {
                return $token;
            }

            if ($innerToken === null) {
                throw new RuntimeException('reached end, expected closing RepeaterToken for ' . $token->name());
            }

            $token->addChildren($innerToken);
        } while (!$innerToken instanceof RepeaterToken || $innerToken->name() !== $token->name());

        return $token;
    }

    private function createVariableToken(string $input, int $start, int $end): VariableToken
    {
        $expression = \trim(\mb_substr($input, $start + 2, $end - $start - 2));

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
