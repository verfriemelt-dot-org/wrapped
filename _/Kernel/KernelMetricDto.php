<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Kernel;

final readonly class KernelMetricDto
{
    public function __construct(
        public float $constructTime,
        public float $requestHandleTime,
        public float $responseTime,
    ) {}
}
