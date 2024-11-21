<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Override;

class HttpRequestException extends RuntimeException implements RequestExceptionInterface
{
    private RequestInterface $request;

    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    #[Override]
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
