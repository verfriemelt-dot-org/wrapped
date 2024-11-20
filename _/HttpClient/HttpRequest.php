<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

final readonly class HttpRequest
{
    /**
     * @param array<string,string> $header
     */
    public function __construct(
        public string $uri,
        public string $method = 'get',
        public array $header = [],
        public ?string $payload = null,
    ) {}
}
