<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr7;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Override;
use InvalidArgumentException;

class Request extends Message implements RequestInterface
{
    private UriInterface $uri;

    private string $requestMethod;
    private string $requestTarget = '/';

    public function __construct(string $method = 'GET', string|UriInterface $uri = '/')
    {
        $this->requestMethod = $method;

        if (is_string($uri)) {
            $this->uri = new Uri($uri);
        }

    }

    #[Override]
    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    #[Override]
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $instance = clone $this;
        $instance->requestTarget = $requestTarget;

        return $instance;
    }

    #[Override]
    public function getMethod(): string
    {
        return $this->requestMethod;
    }

    #[Override]
    public function withMethod(string $method): RequestInterface
    {
        if (\mb_strlen($method) === 0) {
            throw new InvalidArgumentException();
        }

        $instance = clone $this;
        $instance->requestMethod = $method;

        return $instance;
    }

    #[Override]
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    #[Override]
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $instance = clone $this;
        $instance->uri = $uri;
        $instance->requestTarget = $uri->getPath();

        if ($preserveHost === true && ($this->hasHeader('Host') && $this->getHeaderLine('Host') !== '')) {
            return $instance;
        }

        if ($uri->getHost() === '') {
            return $instance;
        }

        return $instance->withHeader('Host', $uri->getHost());
    }
}
