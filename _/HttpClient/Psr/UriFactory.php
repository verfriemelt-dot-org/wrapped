<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Override;

final class UriFactory implements UriFactoryInterface
{
    #[Override]
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
