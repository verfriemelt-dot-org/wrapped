<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use ReflectionClass;
use verfriemelt\wrapped\_\Cli\Console;
use verfriemelt\wrapped\_\Command\Attributes\Command;
use verfriemelt\wrapped\_\Command\Attributes\DefaultCommand;
use verfriemelt\wrapped\_\Command\CommandArguments\Argument;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgvParser;
use verfriemelt\wrapped\_\DI\Container;
use Override;
use RuntimeException;

#[DefaultCommand]
#[Command('help', 'prints out helpful information about commands')]
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

        try {
            $this->describeCommand($cli);
        } catch (RuntimeException $e) {
            $cli->writeLn($e->getMessage());
            return ExitCode::Error;
        }

        return ExitCode::Success;
    }

    private function describeCommand(Console $cli): void
    {
        $name = $this->cmdArgument->get() ?? throw new RuntimeException();
        [$attribute, $command] = $this->findCommandByRoute($name);

        $cli->writeLn('handled by ' . $command::class);
        $cli->eol();
        $cli->write("  {$name}");

        $parser = new ArgvParser();
        $command->configure($parser);
        $parenthesisCount = 0;
        foreach ($parser->arguments() as $arg) {
            $cli->write(' ');
            if (!$arg->required()) {
                ++$parenthesisCount;
                $cli->write('[');
            }

            $cli->write($arg->name);
        }
        $cli->writeLn(\str_repeat(']', $parenthesisCount));
        $cli->eol();

        $cli->writeLn('  ' . $attribute->description);
    }

    /**
     * @return array{Command, AbstractCommand}
     */
    private function findCommandByRoute(string $route): array
    {
        foreach ($this->container->tagIterator(Command::class) as $cmd) {
            $reflection = new ReflectionClass($cmd);
            foreach ($reflection->getAttributes(Command::class) as $attribute) {
                if ($attribute->newInstance()->command === $route) {
                    $instance = $this->container->get($cmd);
                    assert($instance instanceof AbstractCommand);
                    return [$attribute->newInstance(), $instance];
                }
            }
        }

        throw new RuntimeException("command {$route} not found");
    }

    private function listCommands(Console $cli): void
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

        foreach ($commands as $command) {
            $cli->write(\mb_str_pad($command->command, $maxLength + 4));
            $cli->write($command->description);
            $cli->eol();
        }
    }
}
