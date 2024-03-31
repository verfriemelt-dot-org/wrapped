<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use Closure;
use RuntimeException;
use verfriemelt\wrapped\_\Output\Viewable;
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

    /**
     * @param array<string|mixed> $data
     */
    public function render(?array $data = null): string
    {
        if ($data === null) {
            $this->templateRenderer->enableLegacyMode();

            return $this->templateRenderer->render(
                $this->token,
                [
                    'vars' => $this->vars,
                    'if' => $this->if,
                    'repeater' => $this->repeater,
                ]
            );
        }

        return $this->templateRenderer->render($this->token, $data);
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
        if (!\is_scalar($value) && !$value instanceof Viewable && $value !== null && !$value instanceof Closure) {
            throw new RuntimeException('wrong type');
        }

        if (\is_scalar($value) || $value === null) {
            $value = (string) $value;
        }

        $this->vars[$name] = new Variable($name, $value);
        return $this;
    }
}
