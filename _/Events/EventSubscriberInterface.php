<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Events;

interface EventSubscriberInterface
{
    public function on(EventInterface $event): void;
}
