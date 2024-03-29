<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Token\Token;
use RuntimeException;

class TemplateRenderer
{
    private bool $legacyModeEnabled = false;

    public function __construct(
        private readonly Container $container
    ) {}

    public function enableLegacyMode(): void
    {
        $this->legacyModeEnabled = true;
    }

    public function render(
        Token $token,
        array $data
    ): string {
        if ($this->legacyModeEnabled) {
            $processor = new LegacyProcessor($this->container);
        } else {
            throw new RuntimeException('not yet supported');
        }
        return $processor->process($token, $data);
    }
}
