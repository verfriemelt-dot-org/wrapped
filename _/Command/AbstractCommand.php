<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use verfriemelt\wrapped\_\Cli\Console;

abstract readonly class AbstractCommand
{
    abstract public function execute(Console $cli): ExitCode;
}
