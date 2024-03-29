<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel;

interface PropertyObjectInterface
{
    /**
     * creates property from stored value
     */
    public static function hydrateFromString(?string $storedValue): ?static;

    /**
     * returns the object as string
     */
    public function dehydrateToString(): string;
}
