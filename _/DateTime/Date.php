<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DateTime;

use DateTime;
use verfriemelt\wrapped\_\DataModel\PropertyObjectInterface;

final class Date extends DateTime implements PropertyObjectInterface
{
    public const SQL_FORMAT = 'Y-m-d';

    public function toSqlFormat(): string
    {
        return $this->format(static::SQL_FORMAT);
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
