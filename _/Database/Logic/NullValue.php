<?php namespace Wrapped\_\Database\Logic;

    class NullValue extends Value {

        public function __construct() {
            parent::__construct( null );
        }

        public function fetchSqlString(\Wrapped\_\Database\DbLogic $logic) {

            return " NULL ";
        }
    }