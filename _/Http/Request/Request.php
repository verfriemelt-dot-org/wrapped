<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Request;

use verfriemelt\wrapped\_\ParameterBag;
use verfriemelt\wrapped\_\Session\Session;

class Request
{
    private ParameterBag $request;

    private ParameterBag $query;

    private ParameterBag $attributes;

    private ParameterBag $cookies;

    private ParameterBag $server;

    private ParameterBag $files;

    private ParameterBag $content;

    private ParameterBag $header;

    private Session $session;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
    ) {
        $this->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    private function initialize(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
    ) {
        $this->request = new ParameterBag($request);
        $this->query = new ParameterBag($query);
        $this->cookies = new ParameterBag($cookies);
        $this->server = new ParameterBag($server);
        $this->files = new ParameterBag($files);

        if ($content !== null) {
            $contents = json_decode($content);

            if (json_last_error() !== JSON_ERROR_NONE) {
                parse_str($content, $contents);
            }

            $this->content = new ParameterBag((array) $contents);
        } else {
            $this->content = new ParameterBag([]);
        }

        $header = [];

        foreach ($_SERVER as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $header[$key] = $value;
        }

        $this->header = new ParameterBag($header);
        $this->attributes = new ParameterBag($attributes);
    }

    /**
     * get parameters
     */
    public function query(): ParameterBag
    {
        return $this->query;
    }

    /**
     * post parameters
     */
    public function request(): ParameterBag
    {
        return $this->request;
    }

    /**
     * cookies
     */
    public function cookies(): ParameterBag
    {
        return $this->cookies;
    }

    /**
     * $_SERVER variable
     */
    public function server(): ParameterBag
    {
        return $this->server;
    }

    /**
     * $_FILES
     */
    public function files(): ParameterBag
    {
        return $this->files;
    }

    /**
     * rawdata; $_POST
     */
    public function content(): ParameterBag
    {
        return $this->content;
    }

    /**
     * HTTP_ header parsed from server
     */
    public function header(): ParameterBag
    {
        return $this->header;
    }

    /**
     * @return static
     */
    public static function createFromGlobals()
    {
        return new self(
            $_GET,
            $_POST,
            [],
            $_COOKIE,
            $_FILES,
            $_SERVER,
            file_get_contents('php://input'),
        );
    }

    public function secure(): bool
    {
        return $this->server()->get('HTTPS')  === 'on';
    }

    public function uri(): ?string
    {
        $uri = $this->server->get('REQUEST_URI');

        if (str_contains((string) $this->server->get('SERVER_SOFTWARE', ''), 'nginx')) {
            return explode('?', (string) $uri, 2)[0];
        }

        return $this->server->get('REQUEST_URI');
    }

    /**
     * Contains any client-provided pathname information trailing the actual
     * script filename but preceding the query string, if available. For instance,
     * if the current script was accessed via the
     * URL http://www.example.com/php/path_info.php/some/stuff?foo=bar,
     * then $_SERVER['PATH_INFO'] would contain /some/stuff
     */
    public function pathInfo(): ?string
    {
        return $this->server->get('PATH_INFO');
    }

    public function referer(): ?string
    {
        return $this->server->get('HTTP_REFERER');
    }

    public function hostname(): ?string
    {
        return $this->server->get('HTTP_HOST');
    }

    public function requestMethod(): string
    {
        return $this->server->get('REQUEST_METHOD', 'GET');
    }

    public function remoteIp(): ?string
    {
        return $this->server->get('REMOTE_ADDR', '127.0.0.1');
    }

    public function remoteHost(): ?string
    {
        if ($ip = $this->remoteIp()) {
            return gethostbyaddr($ip);
        }

        return null;
    }

    public function userAgent(): ?string
    {
        return $this->server->get('HTTP_USER_AGENT');
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = new ParameterBag($attributes);
        return $this;
    }

    public function attributes(): ParameterBag
    {
        return $this->attributes;
    }

    /**
     * merges the input vectors in that order of priority
     * GET > POST > COOKIE > RAWINPUT
     */
    public function aggregate(): ParameterBag
    {
        return new ParameterBag(
            $this->query->all() + $this->request->all() + $this->cookies->all() + $this->content->all(),
        );
    }

    public function ajax(): bool
    {
        return strtolower($this->server->get('HTTP_X_REQUESTED_WITH') ?? '') === 'xmlhttprequest';
    }

    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function hasSession(): bool
    {
        return isset($this->session);
    }
}
