<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use verfriemelt\pp\Parser\ParserInput;
use verfriemelt\wrapped\_\HttpClient\UriParser;
use RuntimeException;
use Override;

final class Uri implements UriInterface
{
    private string $scheme;
    private string $authority;
    private string $userInfo;
    private string $host;
    private ?int $port;
    private string $path;
    private string $query;
    private string $fragment;

    public function __construct(
        private readonly string $uri,
    ) {
        $this->parse();
    }

    private function parse(): void
    {
        $parser = UriParser::parser();
        $state = $parser->run(new ParserInput($this->uri));

        if ($state->isError()) {
            throw new InvalidArgumentException($state->getError() ?? 'unknown error');
        }

        $results = $state->getResult();

        assert(\is_array($results));
        $assertString = function (mixed $input): string {
            assert(is_string($input));
            return $input;
        };

        $assertInt = function (mixed $input): ?int {
            assert(\is_int($input) || \is_null($input));
            return $input;
        };

        $this->scheme = $assertString($results[0]);
        $this->userInfo = $assertString($results[1]);
        $this->host = $assertString($results[2]);
        $this->port = $assertInt($results[3]);
        $this->path = $assertString($results[4]);
        $this->query = $assertString($results[5]);
        $this->fragment = $assertString($results[6]);

        // fix path
        $this->path = \str_replace(' ', '%20', $this->path);

        $this->authority = '';

        if ($this->userInfo !== '') {
            $this->authority .= $this->userInfo . '@';
        }

        if ($this->host !== '') {
            $this->authority .= $this->host;
        }

        $known = [
            ['http', 80],
            ['https', 443],
        ];

        if (\in_array([$this->scheme, $this->port], $known, true)) {
            $this->port = null;
        }

        if ($this->port !== null) {
            $this->authority .= ':' . ((string) $this->port);
        }
    }

    #[Override]
    public function getScheme(): string
    {
        return $this->scheme;
    }

    #[Override]
    public function getAuthority(): string
    {
        return $this->authority;
    }

    #[Override]
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    #[Override]
    public function getHost(): string
    {
        return $this->host;
    }

    #[Override]
    public function getPort(): ?int
    {
        return $this->port;
    }

    #[Override]
    public function getPath(): string
    {
        return $this->path;
    }

    #[Override]
    public function getQuery(): string
    {
        return $this->query;
    }

    #[Override]
    public function getFragment(): string
    {
        return $this->fragment;
    }

    #[Override]
    public function withScheme(string $scheme): UriInterface
    {
        return new self(
            $this->print(
                $scheme,
                $this->authority,
                $this->userInfo,
                $this->host,
                $this->port,
                $this->path,
                $this->query,
                $this->fragment,
            ),
        );
    }

    #[Override]
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        if (\preg_match('/[^a-zA-Z0-9%\-_\.]/', $user) === 1) {
            $user = \urlencode($user);
        }

        if ($password !== null && \preg_match('/[^a-zA-Z0-9%\-_\.]/', $password) === 1) {
            $password = \urlencode($password);
        }

        $userinfo = $user;

        if ($password !== null) {
            $userinfo .= ':' . $password;
        }

        return new self(
            $this->print(
                $this->scheme,
                $this->authority,
                $userinfo,
                $this->host,
                $this->port,
                $this->path,
                $this->query,
                $this->fragment,
            ),
        );
    }

    #[Override]
    public function withHost(string $host): UriInterface
    {
        return new self(
            $this->print(
                $this->scheme,
                $this->authority,
                $this->userInfo,
                $host,
                $this->port,
                $this->path,
                $this->query,
                $this->fragment,
            ),
        );
    }

    #[Override]
    public function withPort(?int $port): UriInterface
    {
        return new self(
            $this->print(
                $this->scheme,
                $this->authority,
                $this->userInfo,
                $this->host,
                $port,
                $this->path,
                $this->query,
                $this->fragment,
            ),
        );
    }

    #[Override]
    public function withPath(string $path): UriInterface
    {
        $path = \str_replace(' ', '%20', $path);

        return new self(
            $this->print(
                $this->scheme,
                $this->authority,
                $this->userInfo,
                $this->host,
                $this->port,
                $path,
                $this->query,
                $this->fragment,
            ),
        );
    }

    #[Override]
    public function withQuery(string $query): UriInterface
    {
        return new self(
            $this->print(
                $this->scheme,
                $this->authority,
                $this->userInfo,
                $this->host,
                $this->port,
                $this->path,
                $query,
                $this->fragment,
            ),
        );
    }

    #[Override]
    public function withFragment(string $fragment): UriInterface
    {
        return new self(
            $this->print(
                $this->scheme,
                $this->authority,
                $this->userInfo,
                $this->host,
                $this->port,
                $this->path,
                $this->query,
                $fragment,
            ),
        );
    }

    private function print(
        string $scheme,
        string $authority,
        string $userInfo,
        string $host,
        ?int $port,
        string $path,
        string $query,
        string $fragment,
    ): string {
        $uri = '';

        if ($scheme !== '') {
            $uri .= $scheme . '://';
        }

        if ($authority !== '') {
            throw new RuntimeException('not implemented');
        }

        if ($userInfo !== '') {
            $uri .= $userInfo . '@';
        }

        if ($host !== '') {
            $uri .= $host;
        }

        if ($port !== null) {
            $uri .= ':' . $port;
        }

        if ($path !== '') {
            $uri .= $path;
        }

        if ($query !== '') {
            $uri .= '?' . $query;
        }

        if ($fragment !== '') {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->uri;
    }
}
