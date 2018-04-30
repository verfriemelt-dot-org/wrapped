<?php

    namespace Wrapped\_\Database\SQL\Logic;

    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\Driver;

    class Column
    extends LogicItem {

        public function fetchSqlString( DbLogic $logic, Driver $driver ) {

            return $this->tableName !== null ?
                "{$driver->quoteIdentifier( $this->tableName )}.{$driver->quoteIdentifier( $this->getValue() )}" :
                "{$driver->quoteIdentifier( $this->getValue() )}";
        }

    }
