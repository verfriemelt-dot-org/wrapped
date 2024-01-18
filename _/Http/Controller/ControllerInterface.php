<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Controller;

use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Response;

interface ControllerInterface
{
    public function handleRequest(Request $request): Response;
}
