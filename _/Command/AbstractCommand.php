<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use verfriemelt\wrapped\_\Cli\InputInterface;
use verfriemelt\wrapped\_\Cli\OutputInterface;
use verfriemelt\wrapped\_\Command\CommandArguments\Argument;
use verfriemelt\wrapped\_\Command\CommandArguments\Option;

abstract class AbstractCommand
{
    /** @var Argument[] */
    private array $args = [];

    /** @var Option[] */
    private array $options = [];

    protected function addArgument(Argument $argument): void
    {
        $this->args[] = $argument;
    }

    protected function addOption(Option $option): void
    {
        $this->options[] = $option;
    }

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->args;
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function configure(): void {}

    abstract public function execute(InputInterface $input, OutputInterface $output): ExitCode;
}
