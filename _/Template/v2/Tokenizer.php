<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2;

use verfriemelt\wrapped\_\Template\v2\Token\Exception\EmptyContionalExpressionException;
use verfriemelt\wrapped\_\Template\v2\Token\Exception\EmptyRepeaterExpressionException;
use verfriemelt\wrapped\_\Template\v2\Token\Exception\MissingContionalClosingException;
use verfriemelt\wrapped\_\Template\v2\Token\Exception\MissingRepeaterClosingException;
use verfriemelt\wrapped\_\Template\v2\Token\ConditionalToken;
use verfriemelt\wrapped\_\Template\v2\Token\RepeaterToken;
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

        if ($nextTokenStartPos > $this->offset) {
            return $this->createStringToken(\mb_substr($input, $this->offset, $nextTokenStartPos - $this->offset));
        }

        $nextTokenEndPos = \mb_strpos($input, '}}', $nextTokenStartPos);

        \assert(\is_int($nextTokenEndPos), '$nextTokenEndPos must be int');

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

        if (\str_contains($tokenContent, 'if=')) {
            return $this->createConditionalToken($input, $nextTokenStartPos, $nextTokenEndPos);
        }

        throw new Exception('not implemented');
    }

    private function createConditionalToken(string $input, int $start, int $end): ConditionalToken
    {
        $matches = [];
        $expression = \trim(\mb_substr($input, $start + 2, $end - $start - 2));

        if (\preg_match("/.*?(?<closing>\/)?(?<negated>!)?if='(?<expr>.+?)'.*?/", $expression, $matches) !== 1) {
            throw new EmptyContionalExpressionException('cannot match repeater token: ' . $expression);
        }

        \assert(\is_string($matches['expr'] ?? null), 'match expr missing');
        \assert(\is_string($matches['negated'] ?? null), 'match negated missing');
        \assert(\is_string($matches['closing'] ?? null), 'match closing missing');

        $token = new ConditionalToken($this->line, $this->offset, $this->lineOffset);
        $token->setExpression(new Expression($matches['expr'], $matches['negated'] === '!'));

        $this->offset += $end - $start + 2;

        if ($matches['closing'] === '/') {
            return $token;
        }

        do {
            $innerToken = $this->consume($input);

            if ($innerToken instanceof ConditionalToken && $innerToken->expression()->expr === $token->expression()->expr) {
                return $token;
            }

            if ($innerToken === null) {
                throw new MissingContionalClosingException('reached end, expected closing ConditionalToken for ' . $token->expression()->expr);
            }

            $token->addChildren($innerToken);
        } while (!$innerToken instanceof ConditionalToken || $innerToken->expression()->expr !== $token->expression()->expr);

        return $token;
    }

    private function createRepeaterToken(string $input, int $start, int $end): RepeaterToken
    {
        $matches = [];
        $expression = \trim(\mb_substr($input, $start + 2, $end - $start - 2));

        if (\preg_match("/.*?(?<closing>\/)?repeater='(?<name>.+?)'.*?/", $expression, $matches) !== 1) {
            throw new EmptyRepeaterExpressionException('cannot match repeater token: ' . $expression);
        }

        \assert(\is_string($matches['name'] ?? null), 'match name missing');
        \assert(\is_string($matches['closing'] ?? null), 'match closing missing');

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
                throw new MissingRepeaterClosingException('reached end, expected closing RepeaterToken for ' . $token->name());
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
