<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Session;

    interface SessionHandler {

        /**
         * @param type $name
         * @param type $value
         * @param type $persistent
         */
        public function set( $name, $value );

        /**
         * returns the given variable or returns the given default
         * @param type $name
         * @param type $default
         */
        public function get( $name, $default = null );

        /**
         * checks if value has been set
         */
        public function has( $name );

        /**
         * deletes value
         * @param type $name
         */
        public function delete( $name );

        /**
         * destroys current session
         */
        public function destroy();
    }
