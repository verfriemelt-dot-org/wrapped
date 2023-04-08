<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Type;

use verfriemelt\wrapped\_\DataModel\PropertyObjectInterface;

class Json implements PropertyObjectInterface
{
    public $data;

    final public function __construct(string $data)
    {
        $this->data = json_decode($data);
    }

    public function toSqlFormat(): string
    {
        return json_encode($this->data);
    }

    public static function hydrateFromString(?string $storedValue): ?static
    {
        if ($storedValue === null) {
            return null;
        }

        return new static($storedValue);
    }

    public function dehydrateToString(): string
    {
        return $this->toSqlFormat();
    }

    public function toString(): string
    {
        return $this->toSqlFormat();
    }
}
