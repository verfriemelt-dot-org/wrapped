<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Statsd\Connection;

    class UdpSocket
    implements \verfriemelt\wrapped\_\Statsd\Connection {

        private $host;

        private $port;

        private $socket;

        private $isConnected = false;

        public function __construct( $host = "127.0.0.1", $port = 8125 ) {

            $this->host = $host;
            $this->port = $port;
        }

        public function connect() {

            $url = "udp://" . $this->host;

            $errorNumber  = null;
            $errorMessage = null;

            $this->socket = fsockopen( $url, $this->port, $errorNumber, $errorMessage );

            $this->isConnected = true;
            return $this;
        }

        public function send( $message ) {

            if ( $message === '' ) {
                return;
            }

            $this->writeToSocket( $message );
        }

        public function writeToSocket( $message ) {

            if ( !$this->isConnected ) {
                return false;
            }

            try {
                fwrite( $this->socket, $message );
            } catch ( \Exception $e ) {
                return false;
            }

            return true;
        }

    }
