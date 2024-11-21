<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use verfriemelt\wrapped\_\HttpClient\Psr\Response;
use verfriemelt\wrapped\_\HttpClient\Psr\StreamFactory;

trait PsrAdapterTrait
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $header = \array_map(fn ($values) => \implode(',', $values), $request->getHeaders());

        try {
            $response = $this->request(
                (string) $request->getUri(),
                $request->getMethod(),
                $header,
                (string) $request->getBody(),
            );
        } catch (HttpRequestException $e) {
            $e->setRequest($request);
            throw $e;
        }

        $psrResponse = new Response(
            $response->statusCode,
            '',
            $response->header,
        );

        return $psrResponse->withBody((new StreamFactory())->createStream($response->response));
    }
}
