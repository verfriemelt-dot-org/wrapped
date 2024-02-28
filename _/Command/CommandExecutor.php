<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use verfriemelt\wrapped\_\Cli\Console;
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
            [$commandName, $arguments] = $this->getCommandName($cli);

            $commands = $this->commandDiscovery->getCommands();
            $commandInstance = $this->container->get($commands[$commandName] ?? throw new CommandNotFoundException("command {$commandName} not found"));

            \assert($commandInstance instanceof AbstractCommand);

            $parser = new ArgvParser();
            $commandInstance->configure($parser);

            $parser->parse($arguments);
            $exitCode = $commandInstance->execute($cli);

            return $exitCode;
        } catch (ArgumentMissingException|OptionMissingException|CommandNotFoundException $e) {
            $cli->writeLn($e->getMessage(), Console::STYLE_RED);
            $this->printHelp($cli, $e instanceof CommandNotFoundException);

            return ExitCode::Error;
        }
    }

    /**
     * @return array{string,string[]}
     */
    public function getCommandName(Console $cli): array
    {
        $this->container->register(Console::class, $cli);
        $arguments = $cli->getArgv()->all();

        // scriptname
        \array_shift($arguments);

        // command name
        $commandName = \array_shift($arguments) ?? self::DEFAULT_COMMAND;
        \assert(\is_string($commandName));

        // if its an option, readd it to arguments and use default command
        if (\str_starts_with($commandName, '-')) {
            $arguments[] = $commandName;
            $commandName = self::DEFAULT_COMMAND;
        }

        return [$commandName, $arguments];
    }

    public function printHelp(Console $cli, bool $ignoreArguemnt = false): void
    {
        $argvParser = new ArgvParser();
        $argv = [];

        if (!$ignoreArguemnt) {
            $argv[] = $cli->getArgv()->all()[1];
        }

        $helpCommand = $this->container->get(HelpCommand::class);
        assert($helpCommand instanceof HelpCommand);

        $helpCommand->configure($argvParser);
        $argvParser->parse($argv);
        $helpCommand->execute($cli);
    }
}
