<?php

    namespace Wrapped\_\Cli;

    use \Wrapped\_\Http\ParameterBag;

    class Console {

        /**
         *
         * @var ParameterBag
         */
        private $args;

        /**
         *
         * @return \static
         */
        public static function getInstance() {
            return new static( isset( $_SERVER["argv"] ) ? $_SERVER["argv"] : [ ] );
        }

        public function __construct( $args ) {
            $this->args = new ParameterBag( $args );
        }

        public static function isCli() {
            return php_sapi_name() === "cli";
        }

        /**
         *
         * @return ParameterBag
         */
        public function getArgs() {
            return $this->args;
        }

        public function getArgsAsString() {

            return implode(
                " ", 
                // omit first element
                $this->args->except([ 0 ])
            );
        }
    }
