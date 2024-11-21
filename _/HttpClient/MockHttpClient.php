<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

use Override;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

final class MockHttpClient implements HttpClientInterface, ClientInterface
{
    use PsrAdapterTrait;

    /** @var HttpResponse[] */
    private array $responses;

    /** @var HttpRequest[] */
    private array $requests = [];

    public function __construct(
        HttpResponse ...$responses,
    ) {
        $this->responses = $responses;
    }

    public function addResponse(HttpResponse ... $responses): static
    {
        foreach ($responses as $response) {
            $this->responses[] = $response;
        }
        return $this;
    }

    /**
     * @return HttpRequest[]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    #[Override]
    public function request(
        string $uri,
        string $method = 'get',
        array $header = [],
        ?string $payload = null,
    ): HttpResponse {
        $this->requests[] = new HttpRequest($uri, $method, $header, $payload);

        if (count($this->responses) === 0) {
            throw new RuntimeException("no responses left for serving: {$method} {$uri}");
        }

        return \array_shift($this->responses);
    }
}
