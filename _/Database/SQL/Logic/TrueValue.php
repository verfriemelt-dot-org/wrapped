<?php

    namespace Wrapped\_\Database\SQL\Logic;

    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\DatabaseDriver;

    class TrueValue
    extends Value {

        public function __construct() {
            parent::__construct( true );
        }

        public function fetchSqlString( DbLogic $logic, DatabaseDriver $driver ) {

            return " true ";
        }

    }
