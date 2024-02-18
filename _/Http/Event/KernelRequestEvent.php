<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Event;

use verfriemelt\wrapped\_\Events\EventInterface;
use verfriemelt\wrapped\_\Http\Request\Request;

final readonly class KernelRequestEvent implements EventInterface
{
    public function __construct(
        public Request $request
    ) {}
}
