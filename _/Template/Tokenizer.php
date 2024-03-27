<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use Exception;
use verfriemelt\wrapped\_\Template\Token\ConditionalElseToken;
use verfriemelt\wrapped\_\Template\Token\ConditionalToken;
use verfriemelt\wrapped\_\Template\Token\Exception\EmptyContionalExpressionException;
use verfriemelt\wrapped\_\Template\Token\Exception\EmptyRepeaterExpressionException;
use verfriemelt\wrapped\_\Template\Token\Exception\MissingContionalClosingException;
use verfriemelt\wrapped\_\Template\Token\Exception\MissingRepeaterClosingException;
use verfriemelt\wrapped\_\Template\Token\RepeaterToken;
use verfriemelt\wrapped\_\Template\Token\StringToken;
use verfriemelt\wrapped\_\Template\Token\Token;
use verfriemelt\wrapped\_\Template\Token\VariableToken;

final class Tokenizer
{
    private int $line = 0;
    private int $offset = 0;
    private int $lineOffset = 0;

    private static int $templatesParsed = 0;
    private static float $parseTime = 0.0;

    /** @var array<string,Token> */
    private static array $cache = [];

    public function parse(string $input): Token
    {
        return static::$cache[md5($input)] ??= $this->buildAst($input);
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
        $tokenContent = \mb_substr($input, $nextTokenStartPos + 2, $nextTokenEndPos - $nextTokenStartPos - 2);

        if (!\str_contains($tokenContent, '=')) {
            return $this->createVariableToken($input, $nextTokenStartPos, $nextTokenEndPos);
        }

        if (\str_contains($tokenContent, 'repeater=')) {
            return $this->createRepeaterToken($input, $nextTokenStartPos, $nextTokenEndPos);
        }

        if (\str_contains($tokenContent, 'if=') || \str_contains($tokenContent, 'else=')) {
            return $this->createConditionalToken($input, $nextTokenStartPos, $nextTokenEndPos);
        }

        throw new Exception('not implemented');
    }

    private function createConditionalToken(string $input, int $start, int $end): ConditionalToken|ConditionalElseToken
    {
        $matches = [];
        $expression = \trim(\mb_substr($input, $start + 2, $end - $start - 2));

        if (\preg_match("/.*?(?<closing>\/)?(?<negated>!)?(?<type>if|else)='(?<expr>.+?)'.*?/", $expression, $matches) !== 1) {
            throw new EmptyContionalExpressionException('cannot match repeater token: ' . $expression);
        }

        \assert(\is_string($matches['type'] ?? null), 'match type missing');
        \assert(\is_string($matches['expr'] ?? null), 'match expr missing');
        \assert(\is_string($matches['negated'] ?? null), 'match negated missing');
        \assert(\is_string($matches['closing'] ?? null), 'match closing missing');

        if ($matches['type'] === 'if') {
            $token = new ConditionalToken($this->line, $this->offset, $this->lineOffset);
            $token->setExpression(new Expression($matches['expr'], $matches['negated'] === '!'));
        } else {
            $token = new ConditionalElseToken($this->line, $this->offset, $this->lineOffset);
        }

        $this->offset += $end - $start + 2;

        if ($matches['type'] === 'else' || $matches['closing'] === '/') {
            return $token;
        }

        assert($token instanceof ConditionalToken);

        $bag = new Token($this->line, $this->offset, $this->lineOffset);
        $token->setConsequent($bag);

        do {
            $innerToken = $this->consume($input);

            if ($innerToken instanceof ConditionalElseToken) {
                assert(!$token->hasAlternative());

                $bag = new Token();
                $token->setAlternative($bag);
                continue;
            }

            if ($innerToken instanceof ConditionalToken && $innerToken->expression()->expr === $token->expression()->expr) {
                return $token;
            }

            if ($innerToken === null) {
                throw new MissingContionalClosingException('reached end, expected closing ConditionalToken for ' . $token->expression()->expr);
            }

            $bag->addChildren($innerToken);
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
        $defintion = explode('|', \trim(\mb_substr($input, $start + 2, $end - $start - 2)));
        assert(count($defintion) >= 1);
        $expression = $defintion[0];

        $token = new VariableToken($this->line, $this->offset, $this->lineOffset);

        if (isset($defintion[1])) {
            $token->setFormatter($defintion[1]);
        }

        if (\str_starts_with($expression, '!')) {
            $token->setRaw(true);
            $expression = \substr($expression, 1);
        }

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

    public function getPerformanceData(): TokenizerPerformanceDto
    {
        return new TokenizerPerformanceDto(static::$templatesParsed, static::$parseTime);
    }

    public function buildAst(string $input): Token
    {
        $root = new Token();

        ++static::$templatesParsed;
        $timer = \microtime(true);
        while (($token = $this->consume($input)) !== null) {
            $root->addChildren($token);
        }

        static::$parseTime += \microtime(true) - $timer;

        return $root;
    }
}
