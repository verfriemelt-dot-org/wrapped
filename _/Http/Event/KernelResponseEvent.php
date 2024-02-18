<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Event;

use verfriemelt\wrapped\_\Events\EventInterface;
use verfriemelt\wrapped\_\Http\Response\Response;

final readonly class KernelResponseEvent implements EventInterface
{
    public function __construct(
        public Response $response
    ) {}
}
