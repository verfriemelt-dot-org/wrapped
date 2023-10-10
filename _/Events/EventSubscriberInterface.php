<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Events;

use Closure;

interface EventSubscriberInterface
{
    /**
     * @return Closure(EventInterface, mixed ...): EventInterface
     */
    public function on(EventInterface $event): ?Closure;
}
