<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Statsd;

    interface Connection {

        public function send( $message );
    }
