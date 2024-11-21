<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr7;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Override;

final class ResponseFactory implements ResponseFactoryInterface
{
    #[Override]
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response($code, $reasonPhrase);
    }
}
