<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Override;
use InvalidArgumentException;

abstract class Message implements MessageInterface
{
    protected StreamInterface $body;

    /** @var array<string,string[]> */
    private array $headers = [];

    private string $protocolVersion;

    /** @var array<string,string> */
    private array $normalizedHeaderNames = [];

    #[Override]
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    #[Override]
    public function withProtocolVersion(string $version): MessageInterface
    {
        $instance = clone $this;
        $instance->protocolVersion = $version;

        return $instance;
    }

    #[Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }

    #[Override]
    public function hasHeader(string $name): bool
    {
        return \array_key_exists($this->translateHeaderName($name), $this->headers);
    }

    #[Override]
    public function getHeader(string $name): array
    {
        return $this->headers[$this->translateHeaderName($name)] ?? [];
    }

    #[Override]
    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->headers[$this->translateHeaderName($name)] ?? []);
    }

    #[Override]
    public function withHeader(string $name, $value): MessageInterface
    {
        $instance = clone $this;

        if (\mb_strlen($name) === 0) {
            throw new InvalidArgumentException();
        } elseif (!\is_array($value) && !\is_string($value) || $value === []) {
            throw new InvalidArgumentException();
        }

        if (!\is_array($value)) {
            $instance->headers[$this->translateHeaderName($name)] = [$value];
        } else {
            $instance->headers[$this->translateHeaderName($name)] = $value;
        }

        $instance->normalizedHeaderNames[$this->normalizeHeaderName($name)] ??= $name;


        return $instance;
    }

    #[Override]
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $instance = clone $this;

        if (\mb_strlen($name) === 0) {
            throw new InvalidArgumentException();
        } elseif (!\is_array($value) && !\is_string($value) || $value === []) {
            throw new InvalidArgumentException();
        }

        $instance->headers[$this->translateHeaderName($name)] = [
            ... $this->headers[$this->translateHeaderName($name)] ?? [],
            ... (\is_array($value) ? array_values($value) : [$value]),
        ];

        $instance->normalizedHeaderNames[$this->normalizeHeaderName($name)] ??= $name;

        return $instance;

    }

    #[Override]
    public function withoutHeader(string $name): MessageInterface
    {
        $instance = clone $this;

        if (!isset($this->headers[$this->translateHeaderName($name)])) {
            return $instance;
        }

        unset($instance->headers[$this->translateHeaderName($name)]);
        unset($instance->normalizedHeaderNames[$this->normalizeHeaderName($name)]);

        return $instance;
    }

    #[Override]
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    #[Override]
    public function withBody(StreamInterface $body): MessageInterface
    {
        $instance = clone $this;
        $instance->body = $body;

        return $instance;
    }

    protected function translateHeaderName(string $name): string
    {
        return $this->normalizedHeaderNames[$this->normalizeHeaderName($name)] ?? $name;
    }

    protected function normalizeHeaderName(string $name): string
    {
        return strtolower($name);
    }
}
