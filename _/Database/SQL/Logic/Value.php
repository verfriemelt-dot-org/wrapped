<?php

    namespace Wrapped\_\Database\SQL\Logic;

    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\Driver;

    class Value
    extends LogicItem {

        private $isBound   = false;
        private $sqlString = "";

        public function fetchSqlString( DbLogic $logic, Driver $driver ) {

            if ( $this->isBound ) {
                return $this->sqlString;
            }

            $value = $this->getValue();

            // needed for in queries like SELECT * FROM foo WHERE id IN (1,2,3)
            if ( is_array( $value ) ) {

                if ( !empty( $value ) ) {

                    foreach ( $value as &$valueItem ) {
                        // rename for adding the colon!
                        $valueItem = ":" . $logic->bindValue( $valueItem );
                    }

                    $this->sqlString .= "(" . implode( ",", $value ) . ")";
                } else {
                    $this->sqlString .= "('')";
                }
            } else {
                $this->sqlString .= ":" . $logic->bindValue( $value );
            }

            $this->isBound = true;

            return $this->sqlString;
        }

    }
