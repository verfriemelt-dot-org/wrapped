<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use verfriemelt\wrapped\_\Cli\Console;
use verfriemelt\wrapped\_\Cli\ConsoleInput;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgumentMissingException;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgvParser;
use verfriemelt\wrapped\_\Command\CommandArguments\OptionMissingException;
use verfriemelt\wrapped\_\DI\Container;

final readonly class CommandExecutor
{
    final public const string DEFAULT_COMMAND = '_';

    public function __construct(
        private Container $container,
        private CommandDiscovery $commandDiscovery,
    ) {}

    public function execute(Console $cli): ExitCode
    {
        try {
            $commandName = $this->getCommandName($cli);

            $commands = $this->commandDiscovery->getCommands();
            $commandInstance = $this->container->get($commands[$commandName] ?? throw new CommandNotFoundException("command {$commandName} not found"));

            \assert($commandInstance instanceof AbstractCommand);

            $commandInstance->configure();

            $input = ConsoleInput::fromConsole(
                $cli,
                $commandInstance->getArguments(),
                $commandInstance->getOptions(),
            );

            return $commandInstance->execute($input, $cli);
        } catch (ArgumentMissingException|OptionMissingException|CommandNotFoundException $e) {
            $cli->writeLn($e->getMessage(), Console::STYLE_RED);
            $this->printHelp($cli, $e instanceof CommandNotFoundException);

            return ExitCode::Error;
        }
    }

    public function getCommandName(Console $cli): string
    {
        $this->container->register(Console::class, $cli);
        $arguments = $cli->getArgv()->all();

        // scriptname
        \array_shift($arguments);

        // command name
        $commandName = \array_shift($arguments) ?? self::DEFAULT_COMMAND;
        \assert(\is_string($commandName));

        return $commandName;
    }

    public function printHelp(Console $cli, bool $ignoreArguemnt = false): void
    {
        $argv = [];

        if (!$ignoreArguemnt) {
            $argv[] = $cli->getArgv()->all()[1];
        }

        $helpCommand = $this->container->get(HelpCommand::class);
        assert($helpCommand instanceof HelpCommand);
        $helpCommand->configure();

        $argvInstance = new ArgvParser();
        $argvInstance->addArguments(... $helpCommand->getArguments());
        $argvInstance->addOptions(... $helpCommand->getOptions());
        $argvInstance->parse($argv);

        $input = new ConsoleInput($argvInstance);

        $helpCommand->execute($input, $cli);
    }
}
