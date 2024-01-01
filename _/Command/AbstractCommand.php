<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use verfriemelt\wrapped\_\Cli\OutputInterface;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgvParser;

abstract class AbstractCommand
{
    public function configure(ArgvParser $argv): void {}

    abstract public function execute(OutputInterface $output): ExitCode;
}
