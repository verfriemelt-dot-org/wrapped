<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use Exception;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Token\ConditionalToken;
use verfriemelt\wrapped\_\Template\Token\PrintableToken;
use verfriemelt\wrapped\_\Template\Token\RepeaterToken;
use verfriemelt\wrapped\_\Template\Token\StringToken;
use verfriemelt\wrapped\_\Template\Token\Token;
use verfriemelt\wrapped\_\Template\Token\VariableToken;

class TemplateRenderer
{
    private array $repeaterDataSourcePath = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function render(
        Token $token,
        array $data
    ): string {
        return $this->process($token, $data);
    }

    private function process(Token $token, array $data): string
    {
        $output = '';

        if ($token instanceof PrintableToken) {
            $output .= $this->printToken($token, $data);
        }

        if ($token instanceof RepeaterToken) {
            return $output . $this->processRepeaterToken($token, $data);
        }

        if ($token instanceof ConditionalToken) {
            return $output . $this->processConditionalToken($token, $data);
        }

        foreach ($token->children() as $child) {
            $output .= $this->process($child, $data);
        }

        return $output;
    }

    private function printToken(Token $token, array $data): string
    {
        return match ($token::class) {
            StringToken::class => $token->content(),
            VariableToken::class => $this->processVariableToken($token, $data),
            default => throw new TokenizerException('not printable'),
        };
    }

    private function processVariableToken(VariableToken $token, array $data): string
    {
        $name = trim($token->expression()->expr);
        $dataSource = $this->searchForData('vars', $name, $data);

        if ($dataSource === false) {
            return '';
        } else {
            $variable = $dataSource['vars'][$name];
        }

        $output = $variable->readValue();

        if ($token->hasFormatter()) {
            foreach ($this->container->tagIterator(VariableFormatter::class) as $formatterClass) {
                $formatter = $this->container->get($formatterClass);
                \assert($formatter instanceof VariableFormatter);

                if (!$formatter->supports($token->formatter())) {
                    continue;
                }

                $output = $formatter->format($output);
            }
        }

        if (is_object($output)) {
            throw new Exception("object passed to template variable '{$name}'");
        }

        return !$token->raw() ? htmlspecialchars((string) $output, ENT_QUOTES) : $output;
    }

    private function searchForData(string $type, string $name, array $data)
    {
        $layers = [$data];
        $dataSource = $data;

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

    private function processRepeaterToken(RepeaterToken $token, array $data): string
    {
        $dataSource = $this->searchForData('repeater', $token->name(), $data);
        $this->repeaterDataSourcePath[$token->name()] = 0;

        // no data to repeat
        if ($dataSource === false || empty($dataSource['repeater'][$token->name()]->data)) {
            return '';
        }

        $output = '';

        foreach ($dataSource['repeater'][$token->name()]->data as $element) {
            foreach ($token->children() as $innerToken) {
                $output .= $this->process(
                    $innerToken,
                    array_merge_recursive(
                        $element,
                        [
                            'vars' => $data['vars'],
                            'if' => $data['if'],
                        ]
                    )
                );
            }
        }

        return $output;
    }

    private function processConditionalToken(ConditionalToken $token, array $data): string
    {
        $output = '';

        $value = $data['if'][$token->expression()->expr]->bool ?? false;

        if ($value xor $token->expression()->negated) {
            $children = $token->consequent()->children();
        } else {
            $children = $token->hasAlternative() ? $token->alternative()->children() : [];
        }

        foreach ($children as $innerToken) {
            $output .= $this->process(
                $innerToken,
                $data
            );
        }

        return $output;
    }
}
