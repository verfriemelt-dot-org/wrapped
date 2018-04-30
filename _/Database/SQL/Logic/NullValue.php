<?php

    namespace Wrapped\_\Database\SQL\Logic;

    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\Driver;

    class NullValue
    extends Value {

        public function __construct() {
            parent::__construct( null );
        }

        public function fetchSqlString( DbLogic $logic, Driver $driver ) {

            return " NULL ";
        }

    }
