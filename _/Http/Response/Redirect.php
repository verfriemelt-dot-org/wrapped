<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Response;

final class Redirect extends Response
{
    public function __construct(
        private ?string $destination = null
    ) {
        $this->temporarily();
    }

    public function send(): static
    {
        $this->addHeader(new HttpHeader('Location', $this->destination));
        return parent::send();
    }

    public function permanent(): static
    {
        $this->setStatusCode(Http::MOVED_PERMANENTLY);
        return $this;
    }

    public function temporarily(): static
    {
        $this->setStatusCode(Http::TEMPORARY_REDIRECT);
        return $this;
    }

    public function seeOther(string $to): static
    {
        $this->setStatusCode(Http::SEE_OTHER);
        $this->destination = $to;
        return $this;
    }

    public function setDestination(string $path): static
    {
        $this->destination = $path;
        return $this;
    }

    public static function to(string $path): static
    {
        return new static($path);
    }
}
