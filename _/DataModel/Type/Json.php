<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Type;

use verfriemelt\wrapped\_\DataModel\PropertyObjectInterface;
use Override;
use RuntimeException;

class Json implements PropertyObjectInterface
{
    protected mixed $data = null;

    final public function __construct() {}

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

        if (!\json_validate($storedValue)) {
            throw new RuntimeException('illegal json');
        }

        $instance = new static();
        $instance->data = \json_decode($storedValue, true, 512, \JSON_THROW_ON_ERROR);

        return $instance;
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
