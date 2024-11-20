<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

interface HttpClientInterface
{
    /**
     * @param array<string> $header
     *
     * @throws HttpRequestTimeoutException
     */
    public function request(
        string $uri,
        string $method = 'get',
        array $header = [],
        ?string $payload = null,
    ): HttpResponse;
}
