<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Request;

use SplStack;

final readonly class RequestStack
{
    /** @var SplStack<Request> */
    private SplStack $stack;

    public function __construct()
    {
        $this->stack = new SplStack();
    }

    public function push(Request $request): RequestStack
    {
        $this->stack->push($request);
        return $this;
    }

    public function getCurrentRequest(): Request
    {
        return $this->stack->bottom();
    }

    public function pop(): Request
    {
        return $this->stack->pop();
    }
}
