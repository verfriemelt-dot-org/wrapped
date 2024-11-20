<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

use RuntimeException;
use Override;

class CurlHttpClient implements HttpClientInterface
{
    #[Override]
    public function request(
        string $uri,
        string $method = 'get',
        array $header = [],
        ?string $payload = null,
    ): HttpResponse {
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
            static function ($curl, $header) use (&$responseHeaders) {
                $len = \strlen((string) $header);
                $header = \explode(':', (string) $header, 2);

                if (\count($header) < 2) {
                    return $len;
                }

                $name = \strtolower(\trim($header[0]));
                $responseHeaders[$name] = \trim($header[1]);

                return $len;
            },
        );

        switch ($method) {
            case 'get':
                break;
            case 'post':
                \curl_setopt($c, \CURLOPT_POST, true);

                if ($payload !== null) {
                    \curl_setopt($c, \CURLOPT_POSTFIELDS, $payload);
                }

                break;
            default: throw new RuntimeException('method not supported');
        }

        $response = \curl_exec($c);

        if (!\is_string($response)) {
            throw new RuntimeException('request failed');
        }

        if (\curl_errno($c) === 28) {
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
