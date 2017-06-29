<?php namespace Wrapped\_\Database\Logic;

    class Column extends \Wrapped\_\Database\Logic\LogicItem {

        public function fetchSqlString( \Wrapped\_\Database\DbLogic $logic ) {

            return $this->tableName !== null ?
                        "`{$this->tableName}`.`{$this->getValue()}`" :
                        "`{$this->getValue()}`";
        }
    }
