<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Search;

use verfriemelt\wrapped\_\Database\Facade\QueryBuilder;
use verfriemelt\wrapped\_\DataModel\Collection;
use verfriemelt\wrapped\_\DataModel\DataModel;
use verfriemelt\wrapped\_\DataModel\DataModelQueryBuilder;

interface Searchable
{
    public static function search(string $searchString, QueryBuilder $query = null): Collection;

    public static function getSearchFields(): array;

    /**
     * @return DataModelQueryBuilder<DataModel&Searchable>
     */
    public static function buildSelectQuery(): DataModelQueryBuilder;
}
