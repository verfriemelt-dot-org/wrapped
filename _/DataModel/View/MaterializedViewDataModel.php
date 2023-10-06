<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\View;

class MaterializedViewDataModel extends ViewDataModel
{
    public static function refresh(bool $concurrently = true): void
    {
        $database = static::fetchDatabase();
        $tablename = static::fetchTablename();
        $schemaname = static::fetchSchemaname();

        $queryParts = [
            'REFRESH',
            'MATERIALIZED',
            'VIEW',
        ];

        if ($concurrently) {
            $queryParts[] = 'CONCURRENTLY';
        }

        $queryParts[] = "{$schemaname}.{$tablename}";

        $database->query(implode(' ', $queryParts));
    }
}
