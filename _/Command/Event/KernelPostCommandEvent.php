<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command\Event;

use verfriemelt\wrapped\_\Cli\InputInterface;
use verfriemelt\wrapped\_\Cli\OutputInterface;
use verfriemelt\wrapped\_\Command\AbstractCommand;
use verfriemelt\wrapped\_\Events\EventInterface;

final readonly class KernelPostCommandEvent implements EventInterface
{
    public function __construct(
        public InputInterface $input,
        public OutputInterface $output,
        public AbstractCommand $command,
    ) {}
}
