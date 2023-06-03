<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2;

use verfriemelt\wrapped\_\Template\v2\Token\Conditional;
use verfriemelt\wrapped\_\Template\v2\Token\BlockToken;
use verfriemelt\wrapped\_\Template\v2\Token\SimpleToken;
use verfriemelt\wrapped\_\Template\v2\Token\StringToken;
use verfriemelt\wrapped\_\Template\v2\Token\TokenInterface;
use verfriemelt\wrapped\_\Template\v2\Token\VariableToken;

final class Tokenizer
{
    private readonly BlockToken $root;

    /** @var list<TokenInterface> */
    private array $stack;

    private int $currentPosition = 0;
    private readonly int $inputLength;

    public function __construct(
        private readonly string $input
    ) {
        $this->stack[] = $this->root = new BlockToken();
        $this->inputLength = \mb_strlen($this->input);
        $this->parse();
    }

    private const

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

        $content = \mb_substr($this->input, $bracesStart + 2, $bracesEnd - $bracesStart - 2);

        $token = $this->matchVariable($content) ?? $this->matchIf($content);

        if ($token === null) {
            throw new TokenizerException("cant create token from «{$content}»");
        }

        $this->currentPosition = $bracesEnd + 2;

        return $token;
    }

    private function matchVariable(string $content): VariableToken|null
    {
        if (1 !== \preg_match('/^\s?(?<raw>!)?(?<query>[a-zA-Z0-9\.]+)\s?$/', $content, $match)) {
            return null;
        }

        return new VariableToken($match['query'], ($match['raw'] ?? null) === '!');
    }

    private function matchIf(string $content): Conditional|null
    {
        if (1 !== \preg_match('/^\s?if=(?<condition>[0-9a-zA-Z])+\s?$/', $content, $match)) {
            return null;
        }

        return new Conditional($match['condition'], new BlockToken());
    }

    private function matchClosingIf(string $content): Conditional|null
    {
        if (1 !== \preg_match('/^\s?\/if\s?$/', $content, $match)) {
            return null;
        }

        return new Conditional($match['condition'], new BlockToken());
    }

    private function emitStringToken(int $length): StringToken
    {
        return new StringToken(\mb_substr($this->input, $this->currentPosition, $length));
    }

    public function getToken(): BlockToken
    {
        return $this->root;
    }
}
