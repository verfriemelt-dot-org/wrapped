<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Override;

final readonly class Clock implements ClockInterface
{
    #[Override]
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
