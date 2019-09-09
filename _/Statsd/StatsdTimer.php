<?php

    namespace Wrapped\_\Statsd;

    class StatsdTimer {

        private $statsdInstace = null;
        private $start = 0;
        private $diff = null;
        private $name = "";

        public function __construct( StatsdClient $statsd, $key ) {

            $this->statsdInstace = $statsd;
            $this->name = $key;

            $this->restart();
        }

        /**
         * reports back to statsd instance
         */
        public function report() {

            if ( $this->diff === null ) {
                $this->end();
            }

            $this->statsdInstace->send( $this->name, $this->diff, StatsdClient::TIMER_MS );
        }

        /**
         * sets set startingtime to the current time
         */
        public function restart() {
            $this->start = microtime( 1 );
        }

        /**
         *
         * @return int
         */
        public function end() {
            return $this->diff = round( microtime( 1 ) * 1000 ) - round( $this->start * 1000 );
        }

        /**
         *
         * @return int
         */
        public function getTime() {
            return $this->diff;
        }

    }
