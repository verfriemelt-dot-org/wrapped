<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli;

interface OutputInterface
{
    public function write(string $text, ?int $color = null): static;

    public function writeLn(string $text, ?int $color = null): static;

    public function eol(): static;
}
