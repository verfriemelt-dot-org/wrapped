<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

final readonly class Expression
{
    public function __construct(
        public string $expr,
        public bool $negated = false,
    ) {}
}
