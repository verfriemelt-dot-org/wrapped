<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Kernel;

use verfriemelt\wrapped\_\Cli\Console;
use verfriemelt\wrapped\_\Command\ExitCode;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Response;

interface KernelInterface
{
    public function getProjectPath(): string;

    public function getContainer(): Container;

    public function boot(): static;

    public function handle(Request $request): Response;

    public function shutdown(): void;

    public function getMetrics(): KernelMetricDto;

    public function execute(Console $cli): ExitCode;
}
