<?php

declare(strict_types=1);

namespace tests\unit\Event;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Events\EventDispatcher;
use verfriemelt\wrapped\_\Events\EventInterface;
use verfriemelt\wrapped\_\Events\EventSubscriberInterface;

class EventDispatcherTest extends TestCase
{
    public function test_subscriber_beeing_called(): void
    {
        $mockSubscriber = $this->createMock(EventSubscriberInterface::class);
        $mockSubscriber->expects(static::once())->method('on');

        $mockEvent = $this->createMock(EventInterface::class);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($mockSubscriber);
        $dispatcher->dispatch($mockEvent);
    }
}
