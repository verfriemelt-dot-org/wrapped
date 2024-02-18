<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_;

use Closure;
use ErrorException;
use Override;
use Throwable;
use verfriemelt\wrapped\_\Cli\Console;
use verfriemelt\wrapped\_\Command\CommandDiscovery;
use verfriemelt\wrapped\_\Command\CommandExecutor;
use verfriemelt\wrapped\_\Command\ExitCode;
use verfriemelt\wrapped\_\DI\ArgumentMetadataFactory;
use verfriemelt\wrapped\_\DI\ArgumentResolver;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Events\EventDispatcher;
use verfriemelt\wrapped\_\Events\ExceptionEvent;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Http;
use verfriemelt\wrapped\_\Http\Response\Response;
use verfriemelt\wrapped\_\Http\Router\Exception\NoRouteMatching;
use verfriemelt\wrapped\_\Http\Router\Exception\RouteGotFiltered;
use verfriemelt\wrapped\_\Http\Router\Routable;
use verfriemelt\wrapped\_\Http\Router\Router;
use verfriemelt\wrapped\_\Kernel\KernelInterface;
use verfriemelt\wrapped\_\Kernel\KernelResponse;

abstract class AbstractKernel implements KernelInterface
{
    protected Router $router;

    protected Container $container;

    protected EventDispatcher $eventDispatcher;

    private bool $buildInCommandsLoaded = false;

    public function __construct()
    {
        $this->container = new Container();
        $this->container->register(KernelInterface::class, $this);

        $this->router = $this->container->get(Router::class);
        $this->eventDispatcher = $this->container->get(EventDispatcher::class);

        $this->initializeErrorHandling();
    }

    #[Override]
    public function getContainer(): Container
    {
        return $this->container;
    }

    public function loadSetup(string $path): static
    {
        foreach (require $path as $callback) {
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

    /**
     * @param Routable[] $routes
     */
    public function loadRoutes(array $routes): static
    {
        $this->router->add(...$routes);
        return $this;
    }

    protected function build404Response(): Response
    {
        return new Response(Http::NOT_FOUND, '404');
    }

    public function build307Response(): Response
    {
        return new Response(Http::FORBIDDEN, '403');
    }

    public function handle(Request $request): Response
    {
        $this->container->register($request::class, $request);
        $resolver = new ArgumentResolver($this->container, new ArgumentMetadataFactory());

        try {
            $route = $this->router->handleRequest($request);
            $callback = $route->getCallback();

            try {
                if ($callback instanceof Response) {
                    $response = $callback;
                } elseif ($callback instanceof Closure) {
                    $arguments = $resolver->resolv($callback);
                    $response = $callback(...$arguments);
                    $response ??= new Response();
                } else {
                    $response = (new $callback(...$resolver->resolv($callback)))
                        ->setContainer($this->container) // controller container hack :(
                        ->prepare(...$resolver->resolv($callback, 'prepare'))
                        ->handleRequest(...$resolver->resolv($callback, 'handleRequest'));
                }

                $this->triggerKernelResponse($request, $response ?? new Response());
            } catch (Throwable $e) {
                $response = $this->dispatchException($e);
            }

            return $response;
        } catch (NoRouteMatching) {
            return $this->build404Response();
        } catch (RouteGotFiltered $e) {
            if ($e->hasResponse()) {
                return $e->getResponse();
            }

            return $this->build307Response();
        }
    }

    protected function triggerKernelResponse(Request $request, Response $response): void
    {
        $this->eventDispatcher->dispatch((new KernelResponse($request))->setResponse($response));
    }

    protected function dispatchException(Throwable $exception): Response
    {
        $exceptionEvent = $this->eventDispatcher->dispatch(
            new ExceptionEvent($exception, $this->container->get(Request::class))
        );

        if ($exceptionEvent->hasResponse()) {
            return $exceptionEvent->getResponse();
        }

        throw $exceptionEvent->getThrowable();
    }

    protected function initializeErrorHandling(): void
    {
        \set_error_handler(function ($errno, $errstr, $errfile, $errline): never {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        \set_exception_handler(function (Throwable $e): void {
            $this->dispatchException($e);
        });
    }

    public function execute(Console $cli): ExitCode
    {
        $exec = $this->container->get(CommandExecutor::class);
        assert($exec instanceof CommandExecutor);

        return $exec->execute($cli);
    }

    public function loadCommands(string $path, string $pathPrefix, string $namespace): void
    {
        $discovery = $this->container->get(CommandDiscovery::class);
        assert($discovery instanceof CommandDiscovery);

        if ($this->buildInCommandsLoaded === false) {
            $this->buildInCommandsLoaded = true;
            $discovery->loadBuiltInCommands();
        }

        $discovery->findCommands($path, $pathPrefix, $namespace);
        $discovery->loadCommands();
    }
}
