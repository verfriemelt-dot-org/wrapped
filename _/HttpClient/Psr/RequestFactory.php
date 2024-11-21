<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Override;

final class RequestFactory implements RequestFactoryInterface
{
    #[Override]
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }
}
