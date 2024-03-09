<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use Closure;
use verfriemelt\wrapped\_\Output\Viewable;

class Variable implements TemplateItem
{
    /**
     * @param string|Closure(): string|Viewable $value
     */
    public function __construct(
        public readonly string $name,
        private readonly string|Closure|Viewable $value,
    ) {}

    public function readValue(): string
    {
        if ($this->value instanceof Closure) {
            return ($this->value)();
        }

        if ($this->value instanceof Viewable) {
            return $this->value->getContents();
        }

        return $this->value;
    }
}
