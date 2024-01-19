<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Controller\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final readonly class Route
{
    public function __construct(
        public string $path,
    ) {}
}
