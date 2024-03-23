<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Kernel;

use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Response;

interface KernelInterface
{
    public function getProjectPath(): string;

    public function getContainer(): Container;

    public function boot(): void;

    public function handle(Request $request): Response;

    public function shutdown(): void;
}
