<?php namespace Wrapped\_\Cli;

    class ConsoleInput {

        /**
         *
         * @return \static
         */
        public static function createInstance() {
            return new static();
        }

        public function readLn() {
            return fgets(STDIN);
        }

    }