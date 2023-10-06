<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use Exception;
use verfriemelt\wrapped\_\Template\Token\T_IfClose;
use verfriemelt\wrapped\_\Template\Token\T_IfElse;
use verfriemelt\wrapped\_\Template\Token\T_IfOpen;
use verfriemelt\wrapped\_\Template\Token\T_RepeaterClose;
use verfriemelt\wrapped\_\Template\Token\T_RepeaterOpen;
use verfriemelt\wrapped\_\Template\Token\T_String;
use verfriemelt\wrapped\_\Template\Token\T_Variable;
use verfriemelt\wrapped\_\Template\Token\Token;

class TemplateLexer
{
    private string $input = '';

    private int $inputLength = 0;

    private Token $tokenChain;

    private Token $currentToken;

    private int $currentPos = 0;

    private int $currentState = 1;

    // 0 = finished
    // 1 = Open CurlySuchen
    // 2 = CurlyOpenFound -> IF, ELSE, Repeater, Variable -> Closing

    public function setTokenChain(Token $chain): static
    {
        $this->tokenChain = $chain;
        return $this;
    }

    public function lex(string $input): static
    {
        $this->input = $input;
        $this->inputLength = strlen($this->input);

        if ($this->inputLength > 0) {
            return $this->workon();
        }

        $this->tokenChain = new T_String();

        return $this;
    }

    public function workon(): static
    {
        while ($this->currentPos < $this->inputLength) {
            switch ($this->currentState) {
                case 0:
                    break;
                case 1:
                    $this->findOpenCurly();
                    break;
                case 2:
                    $this->findCurlyContent();
                    break;
            }
        }

        return $this;
    }

    private function findCurlyContent(): void
    {
        $closingCurlyPos = strpos($this->input, '}}', $this->currentPos);

        if ($closingCurlyPos === false) {
            new Exception('closing curly missing');
            return;
        }

        $contentBetweenCurlyBraces = substr($this->input, $this->currentPos, $closingCurlyPos - $this->currentPos);
        $hit = false;

        if (empty(trim($contentBetweenCurlyBraces))) {
            $this->currentState = 1;
            $this->currentPos = $closingCurlyPos + 2;
            return;
        }

        if (preg_match(
            "~^ ?(?<negate>!)?(?<close>/)?(?<type>if|else)=['\"](?<name>[a-zA-Z0-9-_]+)['\"] ?$~",
            $contentBetweenCurlyBraces,
            $pregHit
        )) {
            $token = $pregHit['type'] === 'else' ? new T_IfElse() : ($pregHit['close'] === '' ? new T_IfOpen(
            ) : new T_IfClose());
            $token->negated = $pregHit['negate'] !== '';
            $token->currentContent = $pregHit['name'];
            $hit = true;
        }

        if (!$hit && preg_match(
            "~^ ?(?<close>/)?repeater=['\"](?<name>[a-zA-Z0-9-_]+)['\"] ?$~",
            $contentBetweenCurlyBraces,
            $pregHit
        )) {
            $token = $pregHit['close'] === '' ? new T_RepeaterOpen() : new T_RepeaterClose();
            $token->currentContent = $pregHit['name'];

            $this->appendToChain($token);
        }

        if (!$hit && preg_match(
            "~ ?(?<doNotEscape>!)? ?(?<name>[0-9a-zA-Z-_]+)\|?(?<format>[a-zA-Z0-9-_]+)? ?~",
            $contentBetweenCurlyBraces,
            $pregHit
        )) {
            // rest variable
            $token = new T_Variable();
            $token->currentContent = $pregHit['name'];
            $token->escape = $pregHit['doNotEscape'] !== '!';

            if (isset($pregHit['format'])) {
                $token->formatCallback = $pregHit['format'];
            }
        }

        if (!isset($token)) {
            throw new Exception('failed to parse token');
        }

        $this->appendToChain($token);

        $this->currentPos = $closingCurlyPos + 2;
        $this->currentState = 1;

        return;
    }

    private function findOpenCurly(): void
    {
        $pos = strpos($this->input, '{{', $this->currentPos);

        if ($pos === false) {
            $content = substr($this->input, $this->currentPos);
            $token = new T_String();
            $token->currentContent = $content;
            $this->currentPos += strlen($content);
        } else {
            // alles davor is string
            $token = new T_String();
            $token->currentContent = substr($this->input, $this->currentPos, $pos - $this->currentPos);

            $this->currentState = 2;
            $this->currentPos = $pos + 2;
        }

        // zur chain
        $this->appendToChain($token);
    }

    private function appendToChain(Token $token): void
    {
        if (!isset($this->tokenChain)) {
            $this->tokenChain = $token;
        } else {
            $this->currentToken->nextToken = $token;
        }

        $this->currentToken = $token;
    }

    public function printChainInline(?Token $currentToken = null): void
    {
        $currentToken = $currentToken ?: $this->tokenChain;

        echo $currentToken->getTokenName() . '( ' . $currentToken->currentContent . ' )->';

        if ($currentToken->nextToken !== null) {
            $this->printChainInline($currentToken->nextToken);
        }
    }

    public function printChain(?Token $currentToken = null): void
    {
        $currentToken = $currentToken ?: $this->tokenChain;

        echo $currentToken->getTokenName() . ' »' . $currentToken->currentContent . '«' . PHP_EOL;

        if ($currentToken->nextToken !== null) {
            $this->printChain($currentToken->nextToken);
        }
    }

    public function getChain(): Token
    {
        return $this->tokenChain;
    }
}
