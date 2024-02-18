<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Kernel;

use verfriemelt\wrapped\_\DI\Container;

interface KernelInterface
{
    public function getProjectPath(): string;

    public function getContainer(): Container;

    public function boot(): void;
}
