<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr7;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use verfriemelt\wrapped\_\ParameterBag;
use Override;

class ServerRequest extends Request implements ServerRequestInterface
{
    private ParameterBag $query;
    private ParameterBag $attributes;
    private ParameterBag $cookies;
    private ParameterBag $server;
    private ParameterBag $files;
    private ParameterBag $content;

    /**
     * @param array<string|int,mixed> $query
     * @param array<string|int,mixed> $attributes
     * @param array<string|int,mixed> $cookies
     * @param array<string|int,mixed> $server
     * @param array<string|int,mixed> $files
     * @param array<string,string[]>  $headers
     */
    public function __construct(
        string $method = 'GET',
        string|UriInterface $uri = '/',
        array $query = [],
        array $attributes = [],
        array $cookies = [],
        array $server = [],
        array $files = [],
        ?string $content = null,
        array $headers = [],
    ) {
        parent::__construct($method, $uri, $headers);

        $this->query = new ParameterBag($query);
        $this->attributes = new ParameterBag($attributes);
        $this->cookies = new ParameterBag($cookies);
        $this->server = new ParameterBag($server);
        $this->files = new ParameterBag($files);

        if ($content !== null) {
            parse_str($content, $payload);
            $this->content = new ParameterBag($payload);
        } else {
            $this->content = new ParameterBag([]);
        }
    }

    /**
     * @return array<int|string,mixed>
     */
    #[Override]
    public function getServerParams(): array
    {
        return $this->server->all();
    }

    /**
     * @return array<int|string,mixed>
     */
    #[Override]
    public function getCookieParams(): array
    {
        return $this->cookies->all();
    }

    /**
     * @param array<int|string,mixed> $cookies
     */
    #[Override]
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $instance = clone $this;
        $instance->cookies = new ParameterBag($cookies);
        return $instance;
    }

    /**
     * @return array<int|string,mixed>
     */
    #[Override]
    public function getQueryParams(): array
    {
        return $this->query->all();
    }

    /**
     * @param array<int|string,mixed> $query
     */
    #[Override]
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $instance = clone $this;
        $instance->query = new ParameterBag($query);
        return $instance;
    }

    /**
     * @return array<int|string,mixed>
     */
    #[Override]
    public function getUploadedFiles(): array
    {
        return $this->files->all();
    }

    /**
     * @param array<int|string,mixed> $uploadedFiles
     */
    #[Override]
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $instance = clone $this;
        $instance->files = new ParameterBag($uploadedFiles);
        return $instance;
    }

    /**
     * @return array<int|string,mixed>
     */
    #[Override]
    public function getParsedBody()
    {
        return $this->content->all();
    }

    /**
     * @param object|array<int|string,mixed>|null $data
     */
    #[Override]
    public function withParsedBody($data): ServerRequestInterface
    {
        assert(is_array($data));

        $instance = clone $this;
        $instance->content = new ParameterBag($data);
        return $instance;
    }

    /**
     * @return array<int|string,mixed>
     */
    #[Override]
    public function getAttributes(): array
    {
        return $this->attributes->all();
    }

    #[Override]
    public function getAttribute(string $name, $default = null)
    {
        \assert(\is_string($default) || \is_null($default));
        return $this->attributes->get($name, $default);
    }

    #[Override]
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        \assert(\is_string($value) || \is_null($value));
        $instance = clone $this;

        $data = $this->attributes->all();
        $data[$name] = $value;

        $instance->attributes = new ParameterBag($data);
        return $instance;
    }

    #[Override]
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $instance = clone $this;

        $data = $this->attributes->all();
        unset($data[$name]);

        $instance->attributes = new ParameterBag($data);
        return $instance;
    }

    public function __clone(): void
    {
        $this->query = clone $this->query;
        $this->attributes = clone $this->attributes;
        $this->cookies = clone $this->cookies;
        $this->server = clone $this->server;
        $this->files = clone $this->files;
        $this->content = clone $this->content;
    }
}
