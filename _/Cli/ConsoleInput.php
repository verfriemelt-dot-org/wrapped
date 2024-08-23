<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli;

use verfriemelt\wrapped\_\Command\CommandArguments\Argument;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgvParser;
use verfriemelt\wrapped\_\Command\CommandArguments\Option;
use Override;

final readonly class ConsoleInput implements InputInterface
{
    public function __construct(
        public ArgvParser $argv,
    ) {}

    /**
     * @param Argument[] $args
     * @param Option[]   $options
     */
    public static function fromConsole(
        Console $console,
        array $args = [],
        array $options = [],
    ): ConsoleInput {
        $parser = new ArgvParser();
        $parser->addArguments(...$args);
        $parser->addOptions(...$options);
        $parser->parse(\array_slice($console->getArgv()->all(), 2));

        return new self($parser);
    }

    #[Override]
    public function getOption(string $name): Option
    {
        return $this->argv->getOption($name);
    }

    #[Override]
    public function hasOption(string $name): bool
    {
        return $this->argv->hasOption($name);
    }

    #[Override]
    public function getArgument(string $name): Argument
    {
        return $this->argv->getArgument($name);
    }

    #[Override]
    public function hasArgument(string $name): bool
    {
        return $this->argv->hasArgument($name);
    }

    #[Override]
    public function arguments(): array
    {
        return $this->argv->arguments();
    }

    #[Override]
    public function options(): array
    {
        return $this->argv->options();
    }
}
