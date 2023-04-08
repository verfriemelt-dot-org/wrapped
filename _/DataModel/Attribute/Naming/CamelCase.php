<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

use Attribute;

#[Attribute]
class CamelCase extends Convention
{
    public function fetchStringParts(): array
    {
        return array_map('strtolower', preg_split('/(?=[A-Z])/', $this->string));
    }

    public static function fromStringParts(string ...$parts): Convention
    {
        $first = true;
        $string = '';

        foreach ($parts as $part) {
            if ($first) {
                $string .= $part;
            } else {
                $string .= ucfirst($part);
            }

            $first = false;
        }

        return new static($string);
    }
}
