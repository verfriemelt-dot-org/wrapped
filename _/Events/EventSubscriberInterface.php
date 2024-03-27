<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Events;

use Closure;

interface EventSubscriberInterface
{
    /**
     * @template T of EventInterface
     *
     * @param T $event
     *
     * @return Closure(T): T|null
     */
    public function on(EventInterface $event): ?Closure;
}
