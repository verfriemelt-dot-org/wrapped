<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

use Psr\Http\Client\ClientInterface;
use RuntimeException;
use Override;

class CurlHttpClient implements HttpClientInterface, ClientInterface
{
    use PsrAdapterTrait;

    #[Override]
    public function request(
        string $uri,
        string $method = 'GET',
        array $header = [],
        ?string $payload = null,
    ): HttpResponse {

        $method = strtoupper($method);

        if ($uri === '') {
            throw new RuntimeException('empty uri passed');
        }

        $c = \curl_init();

        \curl_setopt($c, \CURLOPT_CONNECTTIMEOUT, 0);
        \curl_setopt($c, \CURLOPT_TIMEOUT, 30);

        // this makes signal handling possible
        \curl_setopt($c, \CURLOPT_PROGRESSFUNCTION, static function () {});

        \curl_setopt($c, \CURLOPT_URL, $uri);
        \curl_setopt($c, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt(
            $c,
            \CURLOPT_USERAGENT,
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.7 Safari/537.36',
        );

        \curl_setopt($c, \CURLOPT_HTTPHEADER, $header);

        $responseHeaders = [];

        \curl_setopt(
            $c,
            \CURLOPT_HEADERFUNCTION,
            static function ($curl, string $header) use (&$responseHeaders) {
                $length = \strlen($header);
                $header = \explode(':', $header, 2);

                if (\count($header) < 2) {
                    return $length;
                }

                $responseHeaders[$header[0]] ??= [];
                $responseHeaders[$header[0]][] = \trim($header[1]);

                return $length;
            },
        );

        if ($method === 'GET' && $payload !== null) {
            throw new RuntimeException('get cannot have payloads');
        }

        if ($method !== 'GET') {

            //            \curl_setopt($c, \CURLOPT_POST, true);

            if ($payload !== null) {
                \curl_setopt($c, \CURLOPT_POSTFIELDS, $payload);
            }

        }

        $response = \curl_exec($c);

        if (!\is_string($response)) {
            throw new RuntimeException('request failed');
        }

        if (\curl_errno($c) === \CURLE_OPERATION_TIMEDOUT) {
            throw new HttpRequestTimeoutException("timeout connecting to: $uri");
        }

        $responseCode = \curl_getinfo($c, \CURLINFO_RESPONSE_CODE);
        assert(\is_int($responseCode));

        return new HttpResponse(
            $responseCode,
            $response,
            $responseHeaders,
        );
    }
}
