<?php

    namespace Wrapped\_\Statsd;

    interface Connection {

        public function send( $message );
    }
    