<?php namespace Wrapped\_\Database\Logic;

    class Value extends \Wrapped\_\Database\Logic\LogicItem {

        private $isBound = false;
        private $sqlString = "";

        public function fetchSqlString( \Wrapped\_\Database\DbLogic $logic) {

            if ( $this->isBound ) {
                return $this->sqlString;
            }

            $value = $this->getValue();

            // needed for in queries like SELECT * FROM foo WHERE id IN (1,2,3)
            if ( is_array($value) ) {

                foreach ($value as &$valueItem) {

                    // rename for adding the colon!
                    $valueItem = ":" . $logic->bindValue( $valueItem );
                }

                $this->sqlString .= "(" . implode(",", $value) . ")";

            } else {
                $this->sqlString .= ":" . $logic->bindValue( $value );
            }

            $this->isBound = true;

            return $this->sqlString;
        }
    }

