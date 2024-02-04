<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

use Attribute;
use Override;

#[Attribute]
class SnakeCase extends Convention
{
    #[Override]
    public function fetchStringParts(): array
    {
        return explode('_', $this->string);
    }

    #[Override]
    public static function fromStringParts(string ...$parts): Convention
    {
        return new static(strtolower(implode('_', $parts)));
    }
}
