<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Controller;

use verfriemelt\wrapped\_\DI\ArgumentResolver;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Http;
use verfriemelt\wrapped\_\Http\Response\Response;
use Override;

abstract class RestController extends Controller implements ControllerInterface
{
    /** @var string[] */
    protected array $supportedVerbs = [
        'GET',
        'POST',
        'DELETE',
        'PUT',
        'PATCH',
    ];

    #[Override]
    public function handleRequest(Request $request): Response
    {
        $verb = $request->requestMethod();

        if (
            in_array($verb, $this->supportedVerbs, true)
            && method_exists($this, $verb)
            && is_callable([$this, $verb])
        ) {
            $argumentResolver = $this->container->get(ArgumentResolver::class);
            $arguments = $argumentResolver->resolv($this, $verb);

            return $this->{$verb}(...$arguments);
        }

        return new Response(Http::NOT_IMPLEMENTED);
    }
}
