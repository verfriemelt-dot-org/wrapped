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
        $command = $this->findCommandByRoute($this->cmdArgument->get() ?? throw new RuntimeException());

        $cli->writeLn($command::class);
    }

    private function findCommandByRoute(string $route): AbstractCommand
    {
        foreach ($this->container->tagIterator(Command::class) as $cmd) {
            $reflection = new ReflectionClass($cmd);
            foreach ($reflection->getAttributes(Command::class) as $attribute) {
                if ($attribute->newInstance()->command === $route) {
                    $instance = $this->container->get($cmd);
                    assert($instance instanceof AbstractCommand);
                    return $instance;
                }
            }
        }

        throw new RuntimeException("command {$route} not found");
    }

    private function listCommands(Console $cli): void
    {
        foreach ($this->container->tagIterator(Command::class) as $cmd) {
            $instance = $this->container->get($cmd);
            \assert($instance instanceof AbstractCommand);

            $attribute = (new ReflectionClass($instance))->getAttributes(Command::class)[0] ?? throw new RuntimeException('missing Command Attribute');
            $parser = new ArgvParser();

            $instance->configure($parser);

            $cli->write($attribute->newInstance()->command);
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
