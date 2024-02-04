<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

use Attribute;
use Override;

#[Attribute]
class PascalCase extends Convention
{
    #[Override]
    public function fetchStringParts(): array
    {
        return array_map('strtolower', preg_split('/(?=[A-Z])/', lcfirst($this->string)));
    }

    #[Override]
    public static function fromStringParts(string ...$parts): Convention
    {
        $string = '';

        foreach ($parts as $part) {
            $string .= ucfirst($part);
        }

        return new static($string);
    }
}
