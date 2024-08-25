<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Events;

use verfriemelt\wrapped\_\DI\ArgumentResolver;

final class EventDispatcher
{
    /** @var EventSubscriberInterface[] */
    protected array $subscriberList = [];

    public function __construct(
        private readonly ArgumentResolver $argumentResolver,
    ) {}

    public function addSubscriber(EventSubscriberInterface $subscriber): static
    {
        $this->subscriberList[] = $subscriber;
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
        foreach ($this->subscriberList as $subscriber) {
            $handler = $subscriber->on($event);
            if ($handler === null) {
                continue;
            }

            $callableArguments = $this->argumentResolver->resolv($handler, skip: 1);
            $handler(
                $event,
                ...$callableArguments,
            );
        }

        return $event;
    }
}
