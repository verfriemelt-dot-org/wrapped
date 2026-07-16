<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Env
{
    public function __construct(
        public string $name,
        public ?string $default = null,
    ) {}
}
