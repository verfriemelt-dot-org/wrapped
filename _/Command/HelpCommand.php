<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use Override;
use ReflectionClass;
use RuntimeException;
use verfriemelt\wrapped\_\Cli\Console;
use verfriemelt\wrapped\_\Cli\InputInterface;
use verfriemelt\wrapped\_\Cli\OutputInterface;
use verfriemelt\wrapped\_\Command\Attributes\Command;
use verfriemelt\wrapped\_\Command\Attributes\DefaultCommand;
use verfriemelt\wrapped\_\Command\CommandArguments\Argument;
use verfriemelt\wrapped\_\DI\Container;

#[DefaultCommand]
#[Command('help', 'prints out helpful information about commands')]
final class HelpCommand extends AbstractCommand
{
    public function __construct(
        private readonly Container $container,
        private readonly CommandDiscovery $commandDiscovery,
    ) {}

    #[Override]
    public function configure(): void
    {
        $this->addArgument(new Argument('command', Argument::OPTIONAL));
    }

    #[Override]
    public function execute(InputInterface $input, OutputInterface $output): ExitCode
    {
        if (!$input->getArgument('command')->present()) {
            $this->listCommands($output);
            return ExitCode::Success;
        }

        try {
            $this->describeCommand($input, $output);
        } catch (RuntimeException $e) {
            $output->writeLn($e->getMessage());
            return ExitCode::Error;
        }

        return ExitCode::Success;
    }

    private function describeCommand(InputInterface $input, OutputInterface $cli): void
    {
        $name = $input->getArgument('command')->get() ?? throw new RuntimeException();
        [$attribute, $command] = $this->findCommandByRoute($name);

        $cli->write($name, Console::STYLE_GREEN);
        $cli->eol();
        $cli->eol();
        $cli->write('handled by ');
        $cli->write($command::class, Console::STYLE_GREEN);

        $command->configure();
        $parenthesisCount = 0;
        foreach ($command->getArguments() as $arg) {
            $cli->write(' ');
            if (!$arg->required()) {
                ++$parenthesisCount;
                $cli->write('[');
            }

            $cli->write("<{$arg->name}>");
        }
        $cli->writeLn(\str_repeat(']', $parenthesisCount));
        $cli->eol();
        $cli->writeLn('  ' . $attribute->description);

        $cli->eol();
        foreach ($command->getOptions() as $opt) {
            $cli->write("  --{$opt->name}", Console::STYLE_GREEN);
            $cli->write("\t\t");
            $cli->write($opt->description ?? '');
            $cli->eol();
        }
        $cli->eol();
        $cli->eol();
    }

    /**
     * @return array{Command, AbstractCommand}
     */
    private function findCommandByRoute(string $route): array
    {
        $commandClass = $this->commandDiscovery->getCommands()[$route] ?? throw new CommandNotFoundException("command {$route} not found");
        $command = $this->container->get($commandClass);

        assert($command instanceof AbstractCommand);

        $reflection = new ReflectionClass($command);
        $attribute = $reflection->getAttributes(Command::class)[0] ?? throw new RuntimeException();

        return [$attribute->newInstance(), $command];
    }

    private function listCommands(OutputInterface $cli): void
    {
        $commands = [];
        $maxLength = 0;

        foreach ($this->container->tagIterator(Command::class) as $cmd) {
            $instance = $this->container->get($cmd);
            \assert($instance instanceof AbstractCommand);

            $attribute = (new ReflectionClass($instance))->getAttributes(Command::class)[0] ?? throw new RuntimeException('missing Command Attribute');

            $command = $attribute->newInstance();
            $commands[] = $command;
            if (\mb_strlen($command->command) > $maxLength) {
                $maxLength = \mb_strlen($command->command);
            }
        }

        usort($commands, static fn (Command $a, Command $b): int => $a->command <=> $b->command);

        $cli->eol();

        foreach ($commands as $command) {
            $cli->write('  ');
            $cli->write(\mb_str_pad($command->command, $maxLength + 4), Console::STYLE_GREEN);
            $cli->write($command->description);
            $cli->eol();
        }

        $cli->eol();
    }
}
