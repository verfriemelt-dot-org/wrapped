<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Override;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @param array<string|int,mixed> $serverParams
     */
    #[Override]
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri, server: $serverParams);
    }
}
