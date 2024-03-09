<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use Exception;
use verfriemelt\wrapped\_\Template\v2\Token\PrintableToken;
use verfriemelt\wrapped\_\Template\v2\Token\StringToken;
use verfriemelt\wrapped\_\Template\v2\Token\Token;
use verfriemelt\wrapped\_\Template\v2\Token\VariableToken;
use verfriemelt\wrapped\_\Template\v2\TokenizerException;

class TemplateRenderer
{
    private array $repeaterDataSourcePath = [];

    public function __construct(
        private readonly Token $token,
        private readonly array $data
    ) {}

    public function render(): string
    {
        return $this->process($this->token);
    }

    private function process(Token $token): string
    {
        $output = '';

        if ($token instanceof PrintableToken) {
            $output .= $this->printToken($token);
        }

        foreach ($token->children() as $child) {
            $output .= $this->process($child);
        }

        return $output;
    }

    private function printToken(Token $token): string
    {
        return match ($token::class) {
            StringToken::class => $token->content(),
            VariableToken::class => $this->parseVar($token),
            default => throw new TokenizerException('not printable'),
        };
    }

    private function parseVar(VariableToken $token): string
    {
        $name = trim((string) $token->expression()->expr);
        //        $outputCallbackPresent = isset($token->formatCallback);

        $dataSource = $this->searchForData('vars', $name);

        if ($dataSource === false) {
            return '';
        } else {
            $variable = $dataSource['vars'][$name];
        }

        //        if ($outputCallbackPresent) {
        //            $output = $variable->readFormattedValue($token->formatCallback);
        //        } else {
        $output = $variable->readValue();
        //        }

        //        if ($variable->getValue() instanceof \verfriemelt\wrapped\_\View\BuiltIns\Link) {
        //            return $output;
        //        }

        if (is_object($output)) {
            throw new Exception("object passed to template variable '{$name}'");
        }

        return !$token->raw() ? htmlspecialchars($output, ENT_QUOTES) : $output;
    }

    private function parseIf()
    {
        $name = $this->currentToken->currentContent;

        $dataSource = $this->searchForData('if', $name);

        // delete all if not there
        if ($dataSource === false) {
            while ($this->currentToken = $this->currentToken->nextToken) {
                // and quit if we hit t_IfClose
                if ($this->currentToken instanceof T_IfClose && $this->currentToken->currentContent === $name) {
                    return '';
                }
            }

            throw new Exception('T_IfClose Missing');
        }

        $bool = $this->currentToken->negated ? !$dataSource['if'][$name]->bool : $dataSource['if'][$name]->bool;
        $output = '';

        while ($this->currentToken = $this->currentToken->nextToken) {
            // if expr is true, collect data
            if ($bool) {
                $output .= $this->parseCurrentToken();
            }

            // we hit else, we switch bool
            if ($this->currentToken instanceof T_IfElse && $this->currentToken->currentContent === $name) {
                $bool = !$bool;
            }

            // we quit
            if ($this->currentToken instanceof T_IfClose && $this->currentToken->currentContent === $name) {
                return $output;
            }
        }

        throw new Exception('T_IfClose Missing');
    }

    public function searchForData($type, $name)
    {
        $layers = [];
        $layers[] = $this->data;
        $dataSource = $this->data;

        // stack repeater on top
        foreach ($this->repeaterDataSourcePath as $repeaterLayer => $currentIndex) {
            // if next level not present, we return the current layer
            if (!isset($dataSource['repeater'][$repeaterLayer]) || !isset($dataSource['repeater'][$repeaterLayer]->data[$currentIndex])) {
                // search from this layer on
                break;
            }

            // go one level deepter into the layers of repeater
            $dataSource = $dataSource['repeater'][$repeaterLayer]->data[$currentIndex];
            $layers[] = $dataSource;
        }

        while ($dataSource = array_pop($layers)) {
            if (isset($dataSource[$type][$name])) {
                return $dataSource;
            }
        }

        return false;
    }

    public function parseRepeater()
    {
        $name = $this->currentToken->currentContent;

        $dataSource = $this->searchForData('repeater', $name);
        $this->repeaterDataSourcePath[$name] = 0;

        // delete all if not there or repeater empty
        if ($dataSource === false || empty($dataSource['repeater'][$name]->data)) {
            while ($this->currentToken = $this->currentToken->nextToken) {
                // and quit if we hit t_IfClose
                if ($this->currentToken instanceof T_RepeaterClose && $this->currentToken->currentContent === $name) {
                    unset($this->repeaterDataSourcePath[$name]);
                    return '';
                }
            }

            throw new Exception('T_RepeaterClose Missing');
        }

        $output = '';
        $startToken = $this->currentToken;

        // circle through datasets
        foreach ($dataSource['repeater'][$name]->data as $index => $dataSource) {
            // set current index for path
            $this->repeaterDataSourcePath[$name] = $index;
            $this->currentToken = $startToken;

            while ($this->currentToken = $this->currentToken->nextToken) {
                $output .= $this->parseCurrentToken();

                if ($this->currentToken instanceof T_RepeaterClose && $this->currentToken->currentContent === $name) {
                    break;
                }
            }
        }

        unset($this->repeaterDataSourcePath[$name]);
        return $output;
    }
}
