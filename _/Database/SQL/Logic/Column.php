<?php namespace Wrapped\_\Database\SQL\Logic;

    class Column extends \Wrapped\_\Database\SQL\Logic\LogicItem {

        public function fetchSqlString( \Wrapped\_\Database\DbLogic $logic ) {

            return $this->tableName !== null ?
                        "`{$this->tableName}`.`{$this->getValue()}`" :
                        "`{$this->getValue()}`";
        }
    }
