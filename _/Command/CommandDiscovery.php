<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

use ReflectionClass;
use verfriemelt\wrapped\_\Command\Attributes\Command;
use verfriemelt\wrapped\_\Command\Attributes\DefaultCommand;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\DI\ServiceDiscovery;

class CommandDiscovery
{
    /** @var array<string,class-string> */
    protected array $commands;

    public function __construct(
        private readonly Container $container
    ) {
        $this->loadBuiltInCommands();
    }

    public function loadBuiltInCommands(): void
    {
        $this->loadCommands(__DIR__, dirname(__DIR__), '\verfriemelt\wrapped\_');
    }

    public function loadCommands(string $path, string $pathPrefix, string $namespace): void
    {
        $discovery = $this->container->get(ServiceDiscovery::class);
        $commands = $discovery->findTags(
            $path,
            $pathPrefix,
            $namespace,
            Command::class
        );

        foreach ($commands as $command) {
            $reflection = new ReflectionClass($command);
            $defaultAttribute = $reflection->getAttributes(DefaultCommand::class)[0] ?? null;
            if ($defaultAttribute !== null) {
                $this->commands[CommandExecutor::DEFAULT_COMMAND] = $command;
            }

            foreach ($reflection->getAttributes(Command::class) as $attribute) {
                $instance = $attribute->newInstance();
                \assert($instance instanceof Command);

                $this->commands[$instance->command] = $command;
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
