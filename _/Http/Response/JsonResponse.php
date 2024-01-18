<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Response;

use verfriemelt\wrapped\_\DataModel\Collection;
use verfriemelt\wrapped\_\DataModel\DataModel;

final class JsonResponse extends Response
{
    private bool $pretty = false;

    public function __construct(mixed $content = null, bool $alreadyEncoded = false)
    {
        $this->addHeader(
            new HttpHeader('Content-type', 'application/json')
        );

        if ($alreadyEncoded) {
            $this->setContent($content);
            return;
        }

        if ($content instanceof Collection) {
            $json = \json_encode($content->toArray());
        } elseif ($content instanceof DataModel) {
            $json = $content->toJson($this->pretty);
        } else {
            $json = \json_encode($content);
        }

        $this->setContent($json);
    }

    public function setContent($content): static
    {
        $this->content = $content;
        return $this;
    }

    public function send(): static
    {
        return parent::send();
    }
}
