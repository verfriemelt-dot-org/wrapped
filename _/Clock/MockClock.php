<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Override;

final readonly class MockClock implements ClockInterface
{
    public function __construct(
        private DateTimeImmutable $clock
    ) {}

    #[Override]
    public function now(): DateTimeImmutable
    {
        return $this->clock;
    }
}
