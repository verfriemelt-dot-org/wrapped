<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Response;

use verfriemelt\wrapped\_\Cli\Console;

class Response
{
    private int $statusCode = Http::OK;

    private string $content = '';

    private array $cookies = [];

    private string $version = '1.1';

    /** @var HttpHeader[] */
    private array $headers = [];

    private ?string $statusText = null;

    private $contentCallback;

    public function __construct(int $statuscode = 200, string $content = null)
    {
        $this->setStatusCode($statuscode);
        $this->setContent($content ?? '');
    }

    public function setStatusCode(int $code): Response
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusText(string $statusText): Response
    {
        $this->statusText = $statusText;
        return $this;
    }

    public function appendContent(string $content): Response
    {
        $this->content .= $content;
        return $this;
    }

    public function setContent($content): Response
    {
        $this->content = $content;
        return $this;
    }

    public function addCookie(Cookie $cookie): Response
    {
        $this->cookies[] = $cookie;
        return $this;
    }

    public function addHeader(HttpHeader $header): Response
    {
        $this->headers[] = $header;
        return $this;
    }

    public function sendHeaders(): Response
    {
        $httpHeader = sprintf(
            'HTTP/%s %s %s',
            $this->version,
            $this->statusCode,
            $this->statusText ?? Http::STATUS_TEXT[$this->statusCode] ?? 'not given'
        );

        // status
        header($httpHeader, true, $this->statusCode);

        foreach ($this->headers as $header) {
            header(
                $header->getName() . ': ' . $header->getValue(),
                $header->replaces()
            );
        }

        // cookies
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie->getName(),
                (string) $cookie->getValue(),
                ['expires' => $cookie->getExpiresTime(), 'path' => $cookie->getPath() ?? '/', 'domain' => $cookie->getDomain() ?? '']
            );
        }

        return $this;
    }

    public function sendContent(): Response
    {
        if ($this->contentCallback !== null) {
            ($this->contentCallback)();
        } else {
            echo $this->content;
        }

        return $this;
    }

    public function send(): Response
    {
        if (!Console::isCli()) {
            $this->sendHeaders();
        }

        return $this->sendContent();
    }

    public function setContentCallback(callable $function): Response
    {
        $this->contentCallback = $function;
        return $this;
    }

    public function getContent(): string {
        return $this->content;
    }
}
