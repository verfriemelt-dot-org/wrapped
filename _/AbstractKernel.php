<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_;

use Closure;
use ErrorException;
use ReflectionAttribute;
use ReflectionClass;
use Throwable;
use verfriemelt\wrapped\_\Cli\Console;
use verfriemelt\wrapped\_\Command\AbstractCommand;
use verfriemelt\wrapped\_\Command\Command;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgvParser;
use verfriemelt\wrapped\_\DI\ArgumentMetadataFactory;
use verfriemelt\wrapped\_\DI\ArgumentResolver;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\DI\ServiceDiscovery;
use verfriemelt\wrapped\_\Events\EventDispatcher;
use verfriemelt\wrapped\_\Events\ExceptionEvent;
use verfriemelt\wrapped\_\Exception\Router\RouteGotFiltered;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Response\Response;
use verfriemelt\wrapped\_\Kernel\KernelInterface;
use verfriemelt\wrapped\_\Kernel\KernelResponse;
use verfriemelt\wrapped\_\Router\Routable;
use verfriemelt\wrapped\_\Router\Route;
use verfriemelt\wrapped\_\Router\Router;

abstract class AbstractKernel implements KernelInterface
{
    protected Router $router;
    protected Router $cliRouter;

    protected Container $container;

    protected EventDispatcher $eventDispatcher;

    public function __construct()
    {
        $this->container = new Container();
        $this->router = new Router();
        $this->cliRouter = new Router();

        $this->eventDispatcher = $this->container->get(EventDispatcher::class);

        $this->initializeErrorHandling();
    }

    public function getContainer(): Container
    {
        return $this->container;
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

    /**
     * @param Routable[] $routes
     */
    public function loadRoutes(array $routes): static
    {
        $this->router->add(...$routes);
        return $this;
    }

    public function handle(Request $request): Response
    {
        $this->container->register($request::class, $request);
        $route = $this->router->handleRequest($request->uri());

        foreach ($route->getAttributes() as $key => $value) {
            $request->attributes()->override($key, $value);
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
                $response ??= new Response();
            } else {
                $resolver = new ArgumentResolver($this->container, new ArgumentMetadataFactory());

                $response = (new $callback(...$resolver->resolv($callback)))
                    ->setContainer($this->container)
                    ->prepare(...$resolver->resolv($callback, 'prepare'))
                    ->handleRequest(...$resolver->resolv($callback, 'handleRequest'));
            }

            $this->triggerKernelResponse($request, $response ?? new Response());
        } catch (Throwable $e) {
            $response = $this->dispatchException($e);
        }

        return $response;
    }

    public function execute(Console $cli): never
    {
        $this->container->register(Console::class, $cli);
        $route = $this->cliRouter->handleRequest($cli->getArgvAsString());
        $command = $this->container->get($route->getCallback());

        \assert($command instanceof AbstractCommand);

        $parser = $this->container->get(ArgvParser::class);
        $command->configure($parser);

        $arguments = $cli->getArgv()->all();
        \array_shift($arguments); // scriptname
        \array_shift($arguments); // command name

        $parser->parse($arguments);

        exit($command->execute($cli)->value);
    }

    protected function triggerKernelResponse(Request $request, Response $response): void
    {
        $this->eventDispatcher->dispatch((new KernelResponse($request))->setResponse($response));
    }

    protected function dispatchException(Throwable $exception): Response
    {
        $exceptionEvent = $this->eventDispatcher->dispatch(new ExceptionEvent($exception, $this->container->get(Request::class)));

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

    public function loadCommands(string $path, string $pathPrefix): void
    {
        $namespace = dirname(\str_replace('\\', '/', static::class));

        $discovery = $this->container->get(ServiceDiscovery::class);
        $commands = $discovery->findTags(
            $path,
            $pathPrefix,
            $namespace,
            Command::class
        );

        foreach ($commands as $command) {
            $reflection = new ReflectionClass($command);
            $attributes = \array_filter($reflection->getAttributes(), fn (ReflectionAttribute $ra): bool => $ra->getName() === Command::class);

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                assert($instance instanceof Command);

                $this->cliRouter->add((new Route($instance->command))->call($command));
            }
        }
    }
}
