<?php namespace Wrapped\_;

    trait Singleton {

        protected static $handle;

        /**
         * nope
         */
        protected function __construct() {}

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