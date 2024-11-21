<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

final readonly class HttpResponse
{
    /**
     * @param array<string,string[]> $header
     */
    public function __construct(
        public int $statusCode,
        public string $response = '',
        public array $header = [],
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'statusCode' => $this->statusCode,
            'header' => \json_encode($this->header, JSON_THROW_ON_ERROR),
            'response' => $this->response,
        ];
    }
}
