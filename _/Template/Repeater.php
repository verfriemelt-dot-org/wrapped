<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use Closure;
use verfriemelt\wrapped\_\Output\Viewable;

class Repeater implements TemplateItem
{
    public array $data = [];

    private array $currentDataLine = [];

    public function __construct(
        public readonly string $name
    ) {}

    public function set(string $name, string|Closure|Viewable $value): static
    {
        $this->currentDataLine['vars'][$name] = new Variable($name, $value);
        return $this;
    }

    public function save(): static
    {
        $this->data[] = $this->currentDataLine;
        $this->currentDataLine = [];
        return $this;
    }

    public function setIf(string $name, bool $bool = true): Repeater
    {
        $this->currentDataLine['if'][$name] = new Ifelse($name, $bool);
        return $this;
    }

    public function createChildRepeater(string $name): Repeater
    {
        if (!isset($this->currentDataLine['repeater'][$name])) {
            $this->currentDataLine['repeater'][$name] = new Repeater($name);
        }

        return $this->currentDataLine['repeater'][$name];
    }
}
