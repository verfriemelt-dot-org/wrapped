<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Kernel;

use Closure;
use Override;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Cli\Console;
use verfriemelt\wrapped\_\Command\Event\KernelPostCommandEvent;
use verfriemelt\wrapped\_\Command\Event\KernelPreCommandEvent;
use verfriemelt\wrapped\_\Events\EventDispatcher;
use verfriemelt\wrapped\_\Events\EventInterface;
use verfriemelt\wrapped\_\Events\EventSubscriberInterface;
use verfriemelt\wrapped\_\Http\Event\KernelRequestEvent;
use verfriemelt\wrapped\_\Http\Event\KernelResponseEvent;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Http;
use verfriemelt\wrapped\_\Kernel\AbstractKernel;
use verfriemelt\wrapped\_\Kernel\KernelInterface;
use verfriemelt\wrapped\_\Session\NullSession;
use verfriemelt\wrapped\_\Session\SessionDataObject;

class KernelTest extends TestCase
{
    private EventDispatcher $eventDispatcher;
    private KernelInterface $kernel;

    /** @var array<class-string<EventInterface>> */
    private array $seenEvents = [];

    #[Override]
    public function setUp(): void
    {
        $this->kernel = new class extends AbstractKernel {
            #[Override]
            public function getProjectPath(): string
            {
                return __DIR__;
            }
        };

        $this->kernel->getContainer()->register(SessionDataObject::class, new NullSession());

        $this->eventDispatcher = $this->kernel->getContainer()->get(EventDispatcher::class);
        $this->kernel->boot();
    }

    #[Override]
    public function tearDown(): void
    {
        $this->kernel->shutdown();
    }

    public function test_for_kernel_request_and_response_event(): void
    {
        $spy = fn (EventInterface $event) => $this->seenEvents[] = $event::class;

        $this->eventDispatcher->addSubscriber(new class ($spy) implements EventSubscriberInterface {
            /**
             * @param Closure(EventInterface): string $spy
             */
            public function __construct(
                public readonly Closure $spy,
            ) {}

            #[Override]
            public function on(EventInterface $event): ?Closure
            {
                ($this->spy)($event);
                return null;
            }
        });

        $this->kernel->handle(new Request(server: ['REQUEST_URI' => '/']));

        static::assertContains(KernelRequestEvent::class, $this->seenEvents);
        static::assertContains(KernelResponseEvent::class, $this->seenEvents);
    }

    public function test_for_kernel_pre_and_post_command_event(): void
    {
        $spy = fn (EventInterface $event) => $this->seenEvents[] = $event::class;

        $this->eventDispatcher->addSubscriber(new class ($spy) implements EventSubscriberInterface {
            /**
             * @param Closure(EventInterface): string $spy
             */
            public function __construct(
                public readonly Closure $spy,
            ) {}

            #[Override]
            public function on(EventInterface $event): ?Closure
            {
                ($this->spy)($event);
                return null;
            }
        });

        $this->kernel->execute(new class (['foo.php', 'help']) extends Console {
            public function write(string $text, ?int $color = null): static
            {
                // nop
                return $this;
            }
        });

        static::assertContains(KernelPreCommandEvent::class, $this->seenEvents);
        static::assertContains(KernelPostCommandEvent::class, $this->seenEvents);
    }

    public function test_for_default_404(): void
    {
        $response = $this->kernel->handle(new Request(server: ['REQUEST_URI' => '/']));

        static::assertSame(Http::NOT_FOUND, $response->getStatusCode());
        static::assertSame('404', $response->getContent());
    }
}
