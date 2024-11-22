<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\HttpClient;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\HttpClient\HttpRequestRetryException;
use verfriemelt\wrapped\_\HttpClient\HttpResponse;
use verfriemelt\wrapped\_\HttpClient\MockHttpClient;
use verfriemelt\wrapped\_\HttpClient\RetryableHttpClient;

class RetryableClientTest extends TestCase
{
    public function test_success_after_retries(): void
    {
        $client = new RetryableHttpClient(
            new MockHttpClient(
                new HttpResponse(500),
                new HttpResponse(500),
                new HttpResponse(200),
            ),
            retryIntervalSeconds: 0,
        );

        static::assertSame(200, $client->request('foo')->statusCode);
    }

    public function test_fail_after_retries(): void
    {
        static::expectException(HttpRequestRetryException::class);

        $client = new RetryableHttpClient(
            new MockHttpClient(
                new HttpResponse(500),
                new HttpResponse(500),
                new HttpResponse(200),
            ),
            retryIntervalSeconds: 0,
            maxRetries: 1,
        );

        $client->request('foo');
    }
}
