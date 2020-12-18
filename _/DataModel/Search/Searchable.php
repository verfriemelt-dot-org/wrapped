<?php

    namespace Wrapped\_\DataModel\Search;

    use \Wrapped\_\Database\Facade\QueryBuilder;
    use \Wrapped\_\DataModel\Collection;

    Interface Searchable {

        static public function search( string $searchString, QueryBuilder $query = null ): Collection;

        static public function getSearchFields(): array;
    }
