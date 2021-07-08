<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_;

    class EnvironmentDetector {

        private $detector = [];

        /**
         *
         * @param string $envName
         * @param callable $func
         * @throws \InvalidArgumentException
         */
        public function registerDetector( $envName, callable $func ) {

            if ( !is_callable( $func ) ) {
                throw new \InvalidArgumentException( "Illegal Detector" );
            }

            self::$detector[$envName] = $func;
        }

        /**
         *
         * @param string $envName
         * @return boolean
         */
        public function is( $envName ) {
            if ( isset( self::$detector[$envName] ) ) {
                $func = self::$detector[$envName];

                return $func();
            }

            return false;
        }

        /**
         *
         * @param string $envName
         * @return boolean
         */
        public function isNot( $envName ) {
            return !self::is( $envName );
        }

        /**
         * for debug only
         * @return mixed
         */
        public function dumpDetectorsResults() {
            $detectors = [];
            foreach ( self::$detector as $name => $func ) {
                $detectors[$name] = $func();
            }

            return $detectors;
        }

    }
