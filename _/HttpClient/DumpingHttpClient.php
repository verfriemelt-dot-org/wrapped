<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

use Override;
use Psr\Http\Client\ClientInterface;

class DumpingHttpClient implements HttpClientInterface, ClientInterface
{
    use PsrAdapterTrait;

    private int $requestNumber = 0;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $path,
    ) {}

    #[Override]
    public function request(
        string $uri,
        string $method = 'get',
        array $header = [],
        ?string $payload = null,
    ): HttpResponse {
        $response = $this->client->request($uri, $method, $header, $payload);

        ++$this->requestNumber;

        \mkdir("{$this->path}/{$this->requestNumber}", recursive: true);

        \file_put_contents("{$this->path}/{$this->requestNumber}/request.json", \json_encode([
            'method' => $method,
            'path' => $uri,
            'header' => $header,
            'payload' => $payload,
        ], JSON_THROW_ON_ERROR));

        \file_put_contents("{$this->path}/{$this->requestNumber}/payload.json", $response->response);
        \file_put_contents("{$this->path}/{$this->requestNumber}/header.json", \json_encode($response->header, \JSON_THROW_ON_ERROR));
        \file_put_contents("{$this->path}/{$this->requestNumber}/statuscode.json", $response->statusCode);

        return $response;
    }
}
