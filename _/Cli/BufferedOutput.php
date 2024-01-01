<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli;

use Override;

final class BufferedOutput implements OutputInterface
{
    private string $buffer = '';

    #[Override]
    public function write(string $text, ?int $color = null): static
    {
        $this->buffer .= $text;
        return $this;
    }

    #[Override]
    public function writeLn(string $text, ?int $color = null): static
    {
        $this->buffer .= $text;
        $this->buffer .= \PHP_EOL;
        return $this;
    }

    #[Override]
    public function eol(): static
    {
        $this->buffer .= \PHP_EOL;
        return $this;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    public function reset(): void
    {
        $this->buffer = '';
    }
}
