<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

class Ifelse implements TemplateItem
{
    public function __construct(
        public readonly string $name,
        public readonly bool $bool = true,
    ) {}
}
