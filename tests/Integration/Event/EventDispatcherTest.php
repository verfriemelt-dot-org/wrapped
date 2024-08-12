<?php

declare(strict_types=1);

namespace tests\unit\Event;

use Closure;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\ArgumentResolver;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Events\EventDispatcher;
use verfriemelt\wrapped\_\Events\EventInterface;
use verfriemelt\wrapped\_\Events\EventSubscriberInterface;

class EventDispatcherTest extends TestCase
{
    private Container $container;
    private EventDispatcher $eventDispatcher;
    private EventSubscriberInterface $eventSubscriber;

    public function setUp(): void
    {
        $this->container = new Container();
        $argumentResolver = $this->container->get(ArgumentResolver::class);

        $this->eventSubscriber = new class implements EventSubscriberInterface {
            public function on(EventInterface $event): ?Closure
            {
                return match (true) {
                    $event instanceof TestEvent => $this->mark(...),
                    $event instanceof SecondTestEvent => $this->withArguments(...),
                    default => null,
                };
            }

            private function mark(TestEvent $event): void
            {
                $event->handled = true;
            }

            private function withArguments(SecondTestEvent $event, Argument $arg): void
            {
                $event->arg = $arg;
            }
        };

        $this->eventDispatcher = new EventDispatcher($argumentResolver);
        $this->eventDispatcher->addSubscriber($this->eventSubscriber);
    }

    public function test_subscriber_beeing_called(): void
    {
        $eventIn = new TestEvent();
        $eventOut = $this->eventDispatcher->dispatch($eventIn);

        static::assertTrue($eventOut->handled);
        static::assertSame($eventIn, $eventOut);
    }

    public function test_subscriber_gets_args_passed(): void
    {
        $arg = $this->container->get(Argument::class);
        $event = $this->eventDispatcher->dispatch(new SecondTestEvent());

        static::assertSame($arg, $event->arg, 'must be the same instance');
    }

    public function test_subcriber_working_with_extended_events(): void
    {
        $eventIn = new ThirdTestEvent();
        $eventOut = $this->eventDispatcher->dispatch($eventIn);

        static::assertTrue($eventOut->handled);
    }
}

class TestEvent implements EventInterface
{
    public bool $handled = false;
}

class SecondTestEvent implements EventInterface
{
    public ?Argument $arg;
}

class Argument {}

class ThirdTestEvent extends TestEvent {}
