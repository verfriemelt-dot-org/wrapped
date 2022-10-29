<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Statsd;

    interface Connection
    {
        public function send($message);
    }
