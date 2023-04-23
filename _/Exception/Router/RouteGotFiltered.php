<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Exception\Router;

use verfriemelt\wrapped\_\Http\Response\Response;

class RouteGotFiltered extends RouterException
{
    private ?Response $response = null;

    public function setResponse(Response $resposne)
    {
        $this->response = $resposne;
    }

    /** @phpstan-assert-if-true !null $this->getResponse() */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
