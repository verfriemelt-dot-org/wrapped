<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_;

use Closure;
use Throwable;
use verfriemelt\wrapped\_\DI\ArgumentMetadataFactory;
use verfriemelt\wrapped\_\DI\ArgumentResolver;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Events\EventDispatcher;
use verfriemelt\wrapped\_\Events\ExceptionEvent;
use verfriemelt\wrapped\_\Exception\Router\RouteGotFiltered;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Response;
use verfriemelt\wrapped\_\Kernel\KernelResponse;
use verfriemelt\wrapped\_\Router\Router;

class Kernel
{
    protected Router $router;

    protected Request $request;

    protected Container $container;

    protected EventDispatcher $eventDispatcher;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();

        $this->container = new Container();
        $this->container->register($this->request::class, $this->request);

        $this->router = $this->container->get(Router::class);
        $this->eventDispatcher = $this->container->get(EventDispatcher::class);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function addAutoloadPath(string $path): static
    {
        spl_autoload_register(function ($class) use ($path) {
            $possiblePath = $path . '/' . str_replace('\\', '/', $class) . '.php';

            if (file_exists($possiblePath)) {
                return require_once $possiblePath;
            }
        });

        return $this;
    }

    public function loadSetup(string $path): static
    {
        foreach (include_once $path as $callback) {
            $resolver = new ArgumentResolver($this->container, new ArgumentMetadataFactory());
            $arguments = $resolver->resolv($callback);

            // run setup
            $callback(...$arguments);
        }

        return $this;
    }

    public function containerConfiguration(Closure $config): static
    {
        $config($this->container);
        return $this;
    }

    public function loadRoutes($routes): static
    {
        $this->router->addRoutes(...$routes);
        return $this;
    }

    public function handle(): ?Response
    {
        $route = $this->router->handleRequest($this->request->uri());

        foreach ($route->getAttributes() as $key => $value) {
            $this->request->attributes()->override($key, $value);
        }

        // router filter
        foreach ($route->getFilters() as $filter) {
            $callbackArguments = $this->container->get(ArgumentResolver::class)->resolv($filter);

            $result = $filter(...$callbackArguments);

            if ($result !== false) {
                $exception = new RouteGotFiltered();

                if ($result instanceof Response) {
                    $exception->setResponse($result);
                }

                throw $exception;
            }
        }

        $callback = $route->getCallback();

        // handle exceptions for 404 and redirect
        try {
            if ($callback instanceof Response) {
                $response = $callback;
            } elseif ($callback instanceof Closure) {
                $resolver = new ArgumentResolver($this->container, new ArgumentMetadataFactory());
                $arguments = $resolver->resolv($callback);
                $response = $callback(...$arguments);
            } else {
                $resolver = new ArgumentResolver($this->container, new ArgumentMetadataFactory());

                $response = (new $callback(...$resolver->resolv($callback)) )
                    ->setContainer($this->container)
                    ->prepare(...$resolver->resolv($callback, 'prepare'))
                    ->handleRequest(...$resolver->resolv($callback, 'handleRequest'));
            }

            $this->triggerKernelResponse($response ?? new Response());
        } catch (Throwable $e) {
            $response = $this->dispatchException($e);
        }

        return $response;
    }

    protected function triggerKernelResponse(Response $response): void
    {
        $this->eventDispatcher->dispatch((new KernelResponse($this->request))->setResponse($response));
    }

    protected function dispatchException(Throwable $exception): Response
    {
        $exceptionEvent = $this->eventDispatcher->dispatch(new ExceptionEvent($exception, $this->request));

        if ($exceptionEvent->hasResponse()) {
            return $exceptionEvent->getResponse();
        }

        throw $exceptionEvent->getThrowable();
    }
}
