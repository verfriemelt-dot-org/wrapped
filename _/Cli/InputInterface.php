<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli;

use verfriemelt\wrapped\_\Command\CommandArguments\Argument;
use verfriemelt\wrapped\_\Command\CommandArguments\Option;

interface InputInterface
{
    public function getOption(string $name): Option;

    public function hasOption(string $name): bool;

    public function getArgument(string $name): Argument;

    public function hasArgument(string $name): bool;

    /**
     * @return Argument[]
     */
    public function arguments(): array;

    /**
     * @return Option[]
     */
    public function options(): array;
}
