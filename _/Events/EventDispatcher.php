<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Events;

class EventDispatcher
{
    /** @var EventSubscriberInterface[] */
    protected array $subscriber = [];

    public function addSubscriber(EventSubscriberInterface $subscriber): static
    {
        $this->subscriber[] = $subscriber;
        return $this;
    }

    /**
     * @template T of EventInterface
     *
     * @param T $event
     *
     * @return T
     */
    public function dispatch(EventInterface $event): EventInterface
    {
        foreach ($this->subscriber as $sub) {
            $sub->on($event);
        }

        return $event;
    }
}
