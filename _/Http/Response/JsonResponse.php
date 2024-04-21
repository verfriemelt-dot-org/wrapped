<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Response;

use verfriemelt\wrapped\_\DataModel\Collection;
use verfriemelt\wrapped\_\DataModel\DataModel;
use Override;

final class JsonResponse extends Response
{
    private bool $pretty = false;

    public function __construct(mixed $content = null, bool $alreadyEncoded = false)
    {
        $this->addHeader(
            new HttpHeader('Content-type', 'application/json'),
        );

        if ($alreadyEncoded) {
            assert(is_string($content), 'must be string when preencoded');
            assert(\json_validate($content), 'invalid json');

            $this->content = $content;
            return;
        }

        $this->setContent($content);
    }

    #[Override]
    public function setContent(mixed $content): static
    {
        if ($content instanceof Collection) {
            $json = \json_encode($content->toArray(), \JSON_THROW_ON_ERROR);
        } elseif ($content instanceof DataModel) {
            $json = $content->toJson($this->pretty);
        } else {
            $json = \json_encode($content, \JSON_THROW_ON_ERROR);
        }

        $this->content = $json;

        return $this;
    }

    #[Override]
    public function send(): static
    {
        return parent::send();
    }
}
