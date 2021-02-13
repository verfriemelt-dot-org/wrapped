<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Statsd;

    class StatsdClient {

        use \Wrapped\_\Singleton;

        CONST COUNTER = "c";

        CONST TIMER_MS = "ms";

        CONST GAUGE = "g";

        private $connection;

        private $namespace = "";

        public function setConnection( Connection $connection ) {
            $this->connection = $connection;
            return $this;
        }

        public function setNamespace( $namespace ) {
            $this->namespace = $namespace;
            return $this;
        }

        /**
         *
         * @param type $key
         * @return \Wrapped\_\Statsd\StatsdClient
         */
        public function incrementCounter( $key ) {
            $this->counter( $key, 1 );
            return $this;
        }

        /**
         *
         * @param type $key
         * @param type $value
         * @return \Wrapped\_\Statsd\StatsdClient
         */
        public function gauge( $key, $value ) {
            $this->send( $key, $value, SELF::GAUGE );
            return $this;
        }

        /**
         *
         * @param type $key
         * @return \Wrapped\_\Statsd\StatsdClient
         */
        public function decrementCounter( $key ) {
            $this->counter( $key, -1 );
            return $this;
        }

        public function counter( $key, $value ) {
            $this->send( $key, $value, SELF::COUNTER );
        }

        /**
         *
         * @param type $key
         * @param \Wrapped\_\Statsd\callable $function
         */
        public function time( $key, callable $function ) {
            $timer = new StatsdTimer( $this, $key );
            $function();
            $timer->report();
        }

        /**
         *
         * @param type $key
         * @return \Wrapped\_\Statsd\StatsdTimer
         */
        public function createTimer( $key ) {
            return new StatsdTimer( $this, $key );
        }

        /**
         * send raw data
         * @param type $key
         * @param type $value
         * @param type $type
         * @return \Wrapped\_\Statsd\StatsdClient
         */
        public function send( $key, $value, $type, $rate = null ) {

            $key = ( $this->namespace !== "") ? "{$this->namespace}.{$key}" : $key;

            if ( $rate !== null ) {
                $message = sprintf( '%s:%s|%s|@%0.1f', $key, $value, $type, $rate );
            } else {
                $message = sprintf( '%s:%s|%s', $key, $value, $type );
            }

            if ( $this->connection ) {
                $this->connection->send( $message );
            }

            return $this;
        }

    }
