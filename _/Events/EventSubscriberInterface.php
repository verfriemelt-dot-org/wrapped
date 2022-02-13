<?php

    namespace verfriemelt\wrapped\_\Events;

    interface EventSubscriberInterface {
        public function on( EventInterface $event ): void;
    }
