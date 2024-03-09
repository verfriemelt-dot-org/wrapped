<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use verfriemelt\wrapped\_\Template\Token\Token;

class Template
{
    private Token $token;

    private array $if = [];
    private array $vars = [];
    private array $repeater = [];

    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
    ) {}

    public function parse(string $template): static
    {
        $tokenizer = new Tokenizer();
        $this->token = $tokenizer->parse($template);

        return $this;
    }

    public function render(): string
    {
        return $this->templateRenderer->render(
            $this->token,
            [
                'vars' => $this->vars,
                'if' => $this->if,
                'repeater' => $this->repeater,
            ]
        );
    }

    public function createRepeater(string $name): Repeater
    {
        if (!isset($this->repeater[$name])) {
            $this->repeater[$name] = new Repeater($name);
        }

        return $this->repeater[$name];
    }

    public function setIf(string $name, bool $bool = true): static
    {
        $this->if[$name] = new Ifelse($name, $bool);
        return $this;
    }

    public function set(string $name, mixed $value): static
    {
        $this->vars[$name] = new Variable($name, $value);
        return $this;
    }
}
