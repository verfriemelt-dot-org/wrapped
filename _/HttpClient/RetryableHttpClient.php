<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

use Closure;
use Override;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

final class RetryableHttpClient implements HttpClientInterface, ClientInterface
{
    use PsrAdapterTrait;

    /** @var Closure(): void */
    private Closure $callback;

    /** @var int[] */
    private array $retriedStatusCodes = [429, 500];

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly int $maxRetries = 10,
        private readonly float $retryIntervalSeconds = 1,
    ) {}

    /**
     * @param int[] $statusCodes
     */
    public function retryOn(array $statusCodes = []): static
    {
        $this->retriedStatusCodes = $statusCodes;
        return $this;
    }

    /**
     * @param Closure(): void $closure
     *
     * @return $this
     */
    public function retryCallable(Closure $closure): static
    {
        $this->callback = $closure;
        return $this;
    }

    #[Override]
    public function request(
        string $uri,
        string $method = 'get',
        array $header = [],
        ?string $payload = null,
    ): HttpResponse {
        $counter = 0;

        request:
        try {
            $response = $this->client->request($uri, $method, $header, $payload);

            if (\in_array($response->statusCode, $this->retriedStatusCodes, true)) {
                throw new HttpRequestRetryException("unexpected code http code: {$response->statusCode}, retrying \n {$response->response}");
            }

            return $response;
        } catch (HttpRequestTimeoutException|HttpRequestRetryException $e) {
            if (++$counter >= $this->maxRetries) {
                throw new HttpRequestRetryException("failed after {$this->maxRetries} retries: {$e->getMessage()}");
            }
            \usleep((int) ($counter * $this->retryIntervalSeconds * 1_000_000));

            if (isset($this->callback)) {
                ($this->callback)();
            }

            goto request;
        }

        throw new RuntimeException('unreacheable');
    }
}
