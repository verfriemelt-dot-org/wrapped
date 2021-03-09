<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel\Search;

    use \verfriemelt\wrapped\_\Database\Facade\QueryBuilder;
    use \verfriemelt\wrapped\_\DataModel\Collection;

    Interface Searchable {

        static public function search( string $searchString, QueryBuilder $query = null ): Collection;

        static public function getSearchFields(): array;
    }
