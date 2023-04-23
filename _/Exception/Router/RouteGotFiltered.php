<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Exception\Router;

use verfriemelt\wrapped\_\Http\Response\Response;

class RouteGotFiltered extends RouterException
{
    private ?\verfriemelt\wrapped\_\Http\Response\Response $response = null;

    public function setResponse(Response $resposne)
    {
        $this->response = $resposne;
    }

    /** @phpstan-assert-if-true !null $this->response */
    public function hasReponse(): bool
    {
        return $this->response instanceof Response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
