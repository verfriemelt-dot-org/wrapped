<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Command;

use Override;
use verfriemelt\wrapped\_\Cli\InputInterface;
use verfriemelt\wrapped\_\Cli\OutputInterface;
use verfriemelt\wrapped\_\Command\AbstractCommand;
use verfriemelt\wrapped\_\Command\Attributes\Command;
use verfriemelt\wrapped\_\Command\ExitCode;
use verfriemelt\wrapped\_\Http\Router\Router;

#[Command('debug:router', 'prints out helpful information about commands')]
final class DebugRouterCommand extends AbstractCommand
{
    public function __construct(
        private readonly Router $router,
    ) {}

    #[Override]
    public function execute(InputInterface $input, OutputInterface $output): ExitCode
    {
        foreach ($this->router->dumpRoutes() as $route) {
            $output->writeLn("{$route->getPath()}");
        }

        return ExitCode::Success;
    }
}
