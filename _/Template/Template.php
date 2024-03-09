<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use verfriemelt\wrapped\_\Template\v2\Token\Token;
use verfriemelt\wrapped\_\Template\v2\Tokenizer;

class Template
{
    private array $if = [];

    private array $vars = [];

    private array $repeater = [];

    private Token $token;

    public function __construct(
        private readonly ?string $path = null,
    ) {}

    public function parse(?string $raw = null): static
    {
        $tokenizer = new Tokenizer();
        $this->token = $tokenizer->parse($raw ?? \file_get_contents($this->path));

        return $this;
    }

    public function render(): string
    {
        if (!isset($this->token)) {
            $this->parse();
        }

        $parser = new TemplateRenderer(
            $this->token,
            [
                'vars' => $this->vars,
                'if' => $this->if,
                'repeater' => $this->repeater,
            ]
        );
        return $parser->render();
    }

    public function yieldRun()
    {
        $parser = (new TemplateRenderer())
            ->setChain($this->tokenChain)
            ->setData(
                [
                    'vars' => $this->vars,
                    'if' => $this->if,
                    'repeater' => $this->repeater,
                ]
            );

        foreach ($parser->alternateParse() as $output) {
            yield $output;
        }
    }

    public function createRepeater(string $name): Repeater
    {
        if (!isset($this->repeater[$name])) {
            $this->repeater[$name] = new Repeater($name);
        }

        return $this->repeater[$name];
    }

    public function mapToRepeater(string $name, string $variable, $data): Repeater
    {
        $r = $this->createRepeater($name);
        array_map(fn ($i) => $r->set($variable, $i)->save(), $data);

        return $r;
    }

    /**
     * @param string $name
     * @param bool   $bool
     */
    public function setIf($name, $bool = true)
    {
        $this->if[$name] = new Ifelse($name, $bool);
        return $this;
    }

    public function set(string $name, mixed $value): static
    {
        $this->vars[$name] = new Variable($name, $value);
        return $this;
    }

    public function setArray($array)
    {
        if (!is_array($array)) {
            return false;
        }

        foreach ($array as $name => $value) {
            $this->set($name, $value);
        }

        return true;
    }
}
