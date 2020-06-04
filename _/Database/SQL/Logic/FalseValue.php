<?php

    namespace Wrapped\_\Database\SQL\Logic;

    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\DatabaseDriver;

    class FalseValue
    extends Value {

        public function __construct() {
            parent::__construct( false );
        }

        public function fetchSqlString( DbLogic $logic, DatabaseDriver $driver ) {

            return " false ";
        }

    }
