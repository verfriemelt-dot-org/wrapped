<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

use Attribute;

#[Attribute]
class PascalCase extends Convention
{
    public function fetchStringParts(): array
    {
        return array_map('strtolower', preg_split('/(?=[A-Z])/', lcfirst($this->string)));
    }

    public static function fromStringParts(string ...$parts): Convention
    {
        $string = '';

        foreach ($parts as $part) {
            $string .= ucfirst($part);
        }

        return new static($string);
    }
}
