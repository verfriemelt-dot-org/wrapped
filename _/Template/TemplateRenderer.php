<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use verfriemelt\wrapped\_\DI\ContainerInterface;
use verfriemelt\wrapped\_\Template\Processor\LegacyProcessor;
use verfriemelt\wrapped\_\Template\Processor\Processor;
use verfriemelt\wrapped\_\Template\Token\Token;

class TemplateRenderer
{
    private bool $legacyModeEnabled = false;

    public function __construct(
        private readonly ContainerInterface $container
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
            $processor = new Processor($this->container);
        }

        return $processor->process($token, $data);
    }
}
