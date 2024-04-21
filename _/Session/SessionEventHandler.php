<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Session;

use Closure;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Events\EventInterface;
use verfriemelt\wrapped\_\Events\EventSubscriberInterface;
use verfriemelt\wrapped\_\Http\Event\KernelRequestEvent;
use verfriemelt\wrapped\_\Http\Event\KernelResponseEvent;
use Override;

final class SessionEventHandler implements EventSubscriberInterface
{
    private Session $session;

    public function __construct(
        private readonly Container $container,
    ) {}

    #[Override]
    public function on(EventInterface $event): ?Closure
    {
        return match (true) {
            $event instanceof KernelRequestEvent => function () use ($event) {
                if (!$this->container->has(SessionDataObject::class)) {
                    return;
                }

                $this->session = new Session($event->request, $this->container->get(SessionDataObject::class));
                $event->request->setSession($this->session);
            },
            $event instanceof KernelResponseEvent => function (KernelResponseEvent $event) {
                if (!$this->container->has(SessionDataObject::class)) {
                    return;
                }

                $this->session->shutdown();
            },
            default => null,
        };
    }
}
