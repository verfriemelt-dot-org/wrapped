<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Search;

use verfriemelt\wrapped\_\Database\Facade\QueryBuilder;
use verfriemelt\wrapped\_\DataModel\Collection;

interface Searchable
{
    /** @return Collection<Searchable> */
    public static function search(string $searchString, QueryBuilder $query = null): Collection;

    public static function getSearchFields(): array;
}
