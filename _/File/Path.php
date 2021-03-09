<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\File;

    class Path {

        /**
         * gets the calling file path
         * @return type
         * @throws \Exception
         */
        public static function getCallerPath() {
            $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );

            if ( !isset( $backtrace[0] ) ) {
                throw new \Exception( "could not determine current backtrace" );
            }

            return dirname( $backtrace[0]["file"] );
        }

    }
