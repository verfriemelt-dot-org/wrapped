<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

final readonly class TokenizerPerformanceDto
{
    public function __construct(
        public int $count,
        public float $totalTime,
    ) {}
}
