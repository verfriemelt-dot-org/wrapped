<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Response;

class Redirect extends Response
{
    public function __construct(
        private ?string $destination = null
    ) {
        $this->temporarily();
    }

    public function send(): Response
    {
        $this->addHeader(new HttpHeader('Location', $this->destination));
        return parent::send();
    }

    public function permanent(): Redirect
    {
        $this->setStatusCode(Http::MOVED_PERMANENTLY);
        return $this;
    }

    public function temporarily(): Redirect
    {
        $this->setStatusCode(Http::TEMPORARY_REDIRECT);
        return $this;
    }

    public function seeOther(string $to): Redirect
    {
        $this->setStatusCode(Http::SEE_OTHER);
        $this->destination = $to;
        return $this;
    }

    public function setDestination(string $path): Redirect
    {
        $this->destination = $path;
        return $this;
    }

    public static function to(string $path): Redirect
    {
        return new self($path);
    }
}
