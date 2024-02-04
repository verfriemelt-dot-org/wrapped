<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

use Attribute;
use Override;

#[Attribute]
class SpaceCase extends Convention
{
    #[Override]
    public function fetchStringParts(): array
    {
        return explode(' ', $this->string);
    }

    #[Override]
    public static function fromStringParts(string ...$parts): Convention
    {
        return new static(implode(' ', array_map('strtolower', $parts)));
    }
}
