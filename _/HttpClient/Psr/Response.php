<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Override;

final class Response extends Message implements ResponseInterface
{
    /**
     * @param array<string,string[]> $headers
     */
    public function __construct(
        public readonly int $statusCode = 200,
        public readonly string $reasonPhrase = '',
        public array $headers = [],
    ) {
        if ($this->statusCode >= 600 || $this->statusCode < 200) {
            throw new InvalidArgumentException('invalid status code');
        }

        parent::__construct($this->headers);
    }

    #[Override]
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    #[Override]
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        return new self($code, $reasonPhrase);
    }

    #[Override]
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
