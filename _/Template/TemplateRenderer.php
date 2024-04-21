<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use verfriemelt\wrapped\_\Template\Processor\LegacyProcessor;
use verfriemelt\wrapped\_\Template\Processor\Processor;
use verfriemelt\wrapped\_\Template\Token\Token;

class TemplateRenderer
{
    private bool $legacyModeEnabled = false;

    public function __construct(
        private readonly LegacyProcessor $legacyProcessor,
        private readonly Processor $processor,
    ) {}

    public function enableLegacyMode(): void
    {
        $this->legacyModeEnabled = true;
    }

    public function render(
        Token $token,
        array $data,
    ): string {
        if ($this->legacyModeEnabled) {
            $processor = $this->legacyProcessor;
        } else {
            $processor = $this->processor;
        }

        return $processor->process($token, $data);
    }
}
