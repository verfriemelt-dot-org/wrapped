<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Controller;

use verfriemelt\wrapped\_\DI\ArgumentResolver;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Response;
use verfriemelt\wrapped\_\Http\Router\Exception\RouterException;
use Override;

abstract class Controller implements ControllerInterface
{
    protected Container $container;

    /**
     * sets the DI Container
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;
        return $this;
    }

    public function prepare(): static
    {
        return $this;
    }

    #[Override]
    public function handleRequest(Request $request): Response
    {
        // used to filter out named regexp hits
        $attributes_on_ints = [];
        $attributes_on_strings = [];

        foreach ($request->attributes()->all() as $index => $value) {
            if (is_integer($index)) {
                $attributes_on_ints[] = $value;
            } else {
                $attributes_on_strings[] = $value;
            }
        }

        $int_only_references = array_diff($attributes_on_ints, $attributes_on_strings);

        $methodName = current(
            array_filter($int_only_references)
        ) ?: 'index'; // $request->attributes()->get( 0, "index" );

        if (!method_exists($this, "handle_{$methodName}") || !is_callable([$this, "handle_{$methodName}"])) {
            throw new RouterException("Method handle_{$methodName} is not callable on " . static::class);
        }

        $method = "handle_{$methodName}";

        $argumentResolver = $this->container->get(ArgumentResolver::class);
        $arguments = $argumentResolver->resolv($this, $method);

        return $this->{$method}(...$arguments);
    }
}
