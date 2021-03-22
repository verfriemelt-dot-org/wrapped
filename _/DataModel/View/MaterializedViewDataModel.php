<?php

    namespace verfriemelt\wrapped\_\DataModel\View;

    class MaterializedViewDataModel
    extends ViewDataModel {

        public static function refresh() {

            $database   = static::fetchDatabase();
            $tablename  = static::fetchTablename();
            $schemaname = static::fetchSchemaname();

            $query = "REFRESH MATERIALIZED VIEW CONCURRENTLY {$schemaname}.{$tablename}";

            $database->query( $query );
        }

    }
