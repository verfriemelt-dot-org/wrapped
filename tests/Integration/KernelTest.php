<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Integration;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Kernel\AbstractKernel;

class KernelTest extends TestCase
{
    public function test_boot_abstract_kernel(): void
    {
        static::expectNotToPerformAssertions();

        $kernel = new class extends AbstractKernel {
            public function getProjectPath(): string
            {
                return 'fake';
            }
        };
        $kernel->shutdown();
    }
}
