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

class TemplateParser
{
    private $chain;

    private $data = [];

    private $currentToken;

    private $repeaterDataSourcePath = [];

    public function setChain(Token $token)
    {
        $this->chain = $token;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function parseCurrentToken()
    {
        if ($this->currentToken instanceof T_String) {
            return $this->currentToken->currentContent;
        }

        if ($this->currentToken instanceof T_Variable) {
            return $this->parseVar();
        }

        if ($this->currentToken instanceof T_IfOpen) {
            return $this->parseIf();
        }

        if ($this->currentToken instanceof T_RepeaterOpen) {
            return $this->parseRepeater();
        }

        return '';
    }

    public function parse()
    {
        $this->currentToken = $this->chain;
        $output = '';

        do {
            $output .= $this->parseCurrentToken();
        } while ($this->currentToken = $this->currentToken->nextToken);

        return $output;
    }

    public function alternateParse()
    {
        $this->currentToken = $this->chain;

        do {
            yield $this->parseCurrentToken();
        } while ($this->currentToken = $this->currentToken->nextToken);
    }

    private function parseVar()
    {
        $name = trim($this->currentToken->currentContent);
        $outputCallbackPresent = isset($this->currentToken->formatCallback);

        $dataSource = $this->searchForData('vars', $name);

        if ($dataSource === false) {
            return '';
        } else {
            $variable = $dataSource['vars'][$name];
        }

        if ($outputCallbackPresent) {
            $output = $variable->readFormattedValue($this->currentToken->formatCallback);
        } else {
            $output = $variable->readValue();
        }

        if ($variable->getValue() instanceof \verfriemelt\wrapped\_\View\BuiltIns\Link) {
            return $output;
        }

        if (is_object($output)) {
            throw new Exception("object passed to template variable '{$name}'");
        }

        return $this->currentToken->escape ? htmlspecialchars((string) $output, ENT_QUOTES) : $output;
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
