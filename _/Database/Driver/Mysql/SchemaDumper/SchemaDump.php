<?php

    namespace Wrapped\_\Database\Driver\Mysql\SchemaDumper;

    use \PDO;
    use \Wrapped\_\Database\Driver\Mysql\Schema;

    class SchemaDump
    extends Schema {

        public function dumpStructure() {

            $res = $this->mainDatabase->query(
                "SELECT COLUMN_NAME, COLUMN_DEFAULT, COLUMN_TYPE, IS_NULLABLE, DATA_TYPE, EXTRA FROM information_schema.COLUMNS " .
                "WHERE " .
                "TABLE_SCHEMA = '{$this->mainDatabase->getCurrentDatabase()}' AND TABLE_NAME = '{$this->tableName}'" );

            return $res->fetchAll( PDO::FETCH_ASSOC );
        }

    }
