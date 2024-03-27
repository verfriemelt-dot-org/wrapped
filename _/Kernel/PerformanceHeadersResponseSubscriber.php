<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Kernel;

use Closure;
use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Events\EventInterface;
use verfriemelt\wrapped\_\Events\EventSubscriberInterface;
use verfriemelt\wrapped\_\Http\Event\KernelResponseEvent;
use verfriemelt\wrapped\_\Http\Response\HttpHeader;
use verfriemelt\wrapped\_\Template\Tokenizer;
use Override;

class PerformanceHeadersResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Tokenizer $tokenizer,
        private readonly KernelInterface $kernel,
    ) {}

    #[Override]
    public function on(EventInterface $event): ?Closure
    {
        return match (true) {
            $event instanceof KernelResponseEvent => function (KernelResponseEvent $event): void {
                $data = [
                    ... $this->getDatabaseTiming($event),
                    ... $this->getTemplateTiming($event),
                    ... $this->getKernelTiming($event),
                ];

                $event->response->addHeader(new HttpHeader('Server-Timing', \implode(', ', $data)));
            },
            default => null
        };
    }

    /**
     * @return string[]
     */
    public function getDatabaseTiming(KernelResponseEvent $event): array
    {
        $metrics = [
            'db-' . DatabaseDriver::$debugQuerieCount => \floor(DatabaseDriver::$time * 1000),
        ];

        return \array_map(static fn (string $m, float|int $v): string => "$m;dur=$v", \array_keys($metrics), \array_values($metrics));
    }

    /**
     * @return string[]
     */
    public function getTemplateTiming(KernelResponseEvent $event): array
    {
        $dto = $this->tokenizer->getPerformanceData();

        $metrics = [
            "template-tokenizer-{$dto->count}" => \floor($dto->totalTime * 1000),
        ];

        return \array_map(static fn (string $m, float|int $v): string => "$m;dur=$v", \array_keys($metrics), \array_values($metrics));
    }

    /**
     * @return string[]
     */
    public function getKernelTiming(KernelResponseEvent $event): array
    {
        $dto = $this->kernel->getMetrics();

        $metrics = [
            'kernel-boot' => \floor(($dto->requestHandleTime - $dto->constructTime) * 1000),
            'kernel-request-handle' => \floor(($dto->responseTime - $dto->requestHandleTime) * 1000),
            'kernel-full' => \floor(($dto->responseTime - $dto->constructTime) * 1000),
        ];

        return \array_map(static fn (string $m, float|int $v): string => "$m;dur=$v", \array_keys($metrics), \array_values($metrics));
    }
}
