<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

use Attribute;
use Override;

#[Attribute]
class LowerCase extends Convention
{
    #[Override]
    public function fetchStringParts(): array
    {
        return [$this->string];
    }

    #[Override]
    protected static function isDestructive(): bool
    {
        return true;
    }

    #[Override]
    public static function fromStringParts(string ...$parts): Convention
    {
        return new static(implode('', array_map(strtolower(...), $parts)));
    }
}
