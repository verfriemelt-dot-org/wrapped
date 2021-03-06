<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_;

    trait Singleton {

        protected static $handle;

        /**
         * nope
         */
        protected function __construct() {

        }

        /**
         * static function for retriving the session handle
         * @return static
         */
        final public static function getInstance( ... $args ) {

            if ( static::$handle === null ) {
                static::$handle = new static( ... $args );
            }

            return static::$handle;
        }

    }
