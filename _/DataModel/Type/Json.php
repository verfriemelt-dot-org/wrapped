<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Type;

use stdClass;
use verfriemelt\wrapped\_\DataModel\PropertyObjectInterface;
use Override;

class Json implements PropertyObjectInterface
{
    public string|stdClass $data;

    final public function __construct(string $data = '{}')
    {
        $this->data = json_decode($data, null, 512, \JSON_THROW_ON_ERROR);
    }

    public function toSqlFormat(): string
    {
        return \json_encode($this->data, \JSON_THROW_ON_ERROR);
    }

    #[Override]
    public static function hydrateFromString(?string $storedValue): ?static
    {
        if ($storedValue === null) {
            return null;
        }

        return new static($storedValue);
    }

    #[Override]
    public function dehydrateToString(): string
    {
        return $this->toSqlFormat();
    }

    public function toString(): string
    {
        return $this->toSqlFormat();
    }

    public function pretty(): string
    {
        return \json_encode($this->data, \JSON_THROW_ON_ERROR ^ \JSON_PRETTY_PRINT);
    }
}
