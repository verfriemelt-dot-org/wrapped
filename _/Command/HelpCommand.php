<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use verfriemelt\wrapped\_\Cli\Console;
use verfriemelt\wrapped\_\Command\Attributes\Command;
use verfriemelt\wrapped\_\Command\CommandArguments\Argument;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgvParser;
use verfriemelt\wrapped\_\DI\Container;
use Override;

#[Command('help')]
final class HelpCommand extends AbstractCommand
{
    private Argument $cmdArgument;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function configure(ArgvParser $argv): void
    {
        $this->cmdArgument = new Argument('command', Argument::OPTIONAL);

        $argv->addArguments($this->cmdArgument);
    }

    #[Override]
    public function execute(Console $cli): ExitCode
    {
        if (!$this->cmdArgument->present()) {
            $this->listCommands($cli);
            return ExitCode::Success;
        }

        $this->describeCommand($cli);
        return ExitCode::Success;
    }

    private function describeCommand(Console $cli): void
    {
        // ???
    }

    private function listCommands(Console $cli): void
    {
        foreach ($this->container->tagIterator(Command::class) as $cmd) {
            $instance = $this->container->get($cmd);
            \assert($instance instanceof AbstractCommand);
            $parser = new ArgvParser();

            $instance->configure($parser);

            $cli->write($instance::class);
            $parenthesisCount = 0;

            foreach ($parser->arguments() as $arg) {
                $cli->write(' ');
                if (!$arg->required()) {
                    ++$parenthesisCount;
                    $cli->write('[');
                }

                $cli->write($arg->name);
            }

            $cli->write(\str_repeat(']', $parenthesisCount));
            $cli->eol();
        }
    }
}
