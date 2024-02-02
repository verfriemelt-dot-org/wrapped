<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use ReflectionClass;
use verfriemelt\wrapped\_\Command\Attributes\Alias;
use verfriemelt\wrapped\_\Command\Attributes\Command;
use verfriemelt\wrapped\_\Command\Attributes\DefaultCommand;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\DI\ServiceDiscovery;
use RuntimeException;

class CommandDiscovery
{
    /** @var array<string,class-string> */
    protected array $commands;

    public function __construct(
        private readonly Container $container,
        private readonly ServiceDiscovery $serviceDiscovery,
    ) {}

    public function loadBuiltInCommands(): void
    {
        $this->findCommands(dirname(__DIR__), dirname(__DIR__, 2), '\verfriemelt\wrapped');
    }

    public function findCommands(string $path, string $pathPrefix, string $namespace): void
    {
        $this->serviceDiscovery->findTaggedServices(
            $path,
            $pathPrefix,
            $namespace,
            Command::class
        );
    }

    public function loadCommands(): void
    {
        $commands = $this->container->tagIterator(Command::class);

        foreach ($commands as $commandClass) {
            $reflection = new ReflectionClass($commandClass);

            $commandAttribute = $reflection->getAttributes(Command::class)[0] ?? null;
            if ($commandAttribute === null) {
                continue;
            }

            if (null !== ($reflection->getAttributes(DefaultCommand::class)[0] ?? null)) {
                $this->commands[CommandExecutor::DEFAULT_COMMAND] = $commandClass;
            }

            $instance = $commandAttribute->newInstance();
            \assert($instance instanceof Command);

            if (isset($this->commands[$instance->command])) {
                throw new RuntimeException("command {$instance->command} already registered");
            }

            $this->commands[$instance->command] = $commandClass;

            foreach ($reflection->getAttributes(Alias::class) as $aliasAttribute) {
                $alias = $aliasAttribute->newInstance();

                if (isset($this->commands[$alias->alias])) {
                    throw new RuntimeException("command {$alias->alias} already registered");
                }

                $this->commands[$alias->alias] = $commandClass;
            }
        }
    }

    /**
     * @return array<string,class-string>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
