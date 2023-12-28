<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use verfriemelt\wrapped\_\Cli\Console;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgvParser;

abstract class AbstractCommand
{
    public function configure(ArgvParser $argv): void {}

    abstract public function execute(Console $cli): ExitCode;
}
