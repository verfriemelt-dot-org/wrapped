<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2;

use verfriemelt\wrapped\_\Template\v2\Token\RootToken;
use verfriemelt\wrapped\_\Template\v2\Token\SimpleToken;
use verfriemelt\wrapped\_\Template\v2\Token\StringToken;
use verfriemelt\wrapped\_\Template\v2\Token\TokenInterface;
use verfriemelt\wrapped\_\Template\v2\Token\VariableToken;

final class Tokenizer
{
    private readonly RootToken $root;

    /** @var list<TokenInterface> */
    private array $stack;

    private int $currentPosition = 0;
    private readonly int $inputLength;

    public function __construct(
        private readonly string $input
    ) {
        $this->stack[] = $this->root = new RootToken();
        $this->inputLength = \mb_strlen($this->input);
        $this->parse();
    }

    private function parse(): void
    {
        while ($token = $this->consume()) {
            $this->appendToken($token);

            if (!$token instanceof SimpleToken) {
                $this->stack[] = $token;
            }
        }
    }

    private function appendToken(TokenInterface $token): void
    {
        $this->stack[count($this->stack) - 1]->addChildren($token);
    }

    private function getCurrentToken(): TokenInterface
    {
        return $this->stack[count($this->stack) - 1];
    }

    private function consume(): ?TokenInterface
    {
        if ($this->currentPosition >= $this->inputLength) {
            return null;
        }

        $bracesStart = \mb_strpos($this->input, '{{', $this->currentPosition);

        if ($bracesStart === false) {
            $token = $this->emitStringToken($this->inputLength - $this->currentPosition);
            $this->currentPosition = $this->inputLength;
            return $token;
        }

        $bracesEnd = \mb_strpos($this->input, '}}', $bracesStart);

        if ($bracesEnd === false) {
            throw new TokenizerException("missing }} at {$this->currentPosition}");
        }

        if ($this->currentPosition < $bracesStart - 1) {
            $this->appendToken($this->emitStringToken($bracesStart - $this->currentPosition));
        }

        $bracesContent = \mb_substr($this->input, $bracesStart + 2, $bracesEnd - 2);

        $token = $this->matchVariable($bracesContent);

        if ($token === null) {
            throw new TokenizerException('cant create token');
        }

        $this->currentPosition = $bracesEnd + 2;

        return $token;
    }

    private function matchVariable(string $content): VariableToken|null
    {
        if (1 !== \preg_match('/(?<raw>!)?(?<query>[a-zA-Z0-9\.]+)/', $content, $match)) {
            return null;
        }

        return new VariableToken($match['query'], ($match['raw'] ?? null) === '!');
    }

    private function matchBranch(string $content): VariableToken|null
    {
        if (1 !== \preg_match('/(?<raw>!)?(?<query>[a-zA-Z0-9\.]+)/', $content, $match)) {
            return null;
        }

        return new VariableToken($match['query'], ($match['raw'] ?? null) === '!');
    }

    private function emitStringToken(int $length): StringToken
    {
        return new StringToken(\mb_substr($this->input, $this->currentPosition, $length));
    }

    public function getToken(): RootToken
    {
        return $this->root;
    }
}
