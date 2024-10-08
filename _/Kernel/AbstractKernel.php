<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Kernel;

use Closure;
use ErrorException;
use Override;
use RuntimeException;
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
use verfriemelt\wrapped\_\Formular\FormTypes\Button;
use verfriemelt\wrapped\_\Formular\FormTypes\Checkbox;
use verfriemelt\wrapped\_\Formular\FormTypes\Date;
use verfriemelt\wrapped\_\Formular\FormTypes\Hidden;
use verfriemelt\wrapped\_\Formular\FormTypes\Password;
use verfriemelt\wrapped\_\Formular\FormTypes\Select;
use verfriemelt\wrapped\_\Formular\FormTypes\Text;
use verfriemelt\wrapped\_\Formular\FormTypes\Textarea;
use verfriemelt\wrapped\_\Formular\Formular;
use verfriemelt\wrapped\_\Http\Event\KernelRequestEvent;
use verfriemelt\wrapped\_\Http\Event\KernelResponseEvent;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Request\RequestStack;
use verfriemelt\wrapped\_\Http\Response\Http;
use verfriemelt\wrapped\_\Http\Response\Response;
use verfriemelt\wrapped\_\Http\Router\Exception\NoRouteMatching;
use verfriemelt\wrapped\_\Http\Router\Exception\RouteGotFiltered;
use verfriemelt\wrapped\_\Http\Router\Routable;
use verfriemelt\wrapped\_\Http\Router\Router;
use verfriemelt\wrapped\_\Session\SessionEventHandler;
use verfriemelt\wrapped\_\Template\Template;

abstract class AbstractKernel implements KernelInterface
{
    protected Router $router;

    protected Container $container;

    protected EventDispatcher $eventDispatcher;

    protected bool $booted = false;

    protected readonly float $constructTime;
    protected float $requestHandleTime;
    protected float $responseTime;

    public function __construct()
    {
        $this->constructTime = \microtime(true);
        $this->container = new Container();

        $this->container->register(KernelInterface::class, $this);

        $this->container->register(Template::class)->share(false);

        // formular stuff
        $this->container->register(Formular::class)->share(false);
        $this->container->register(Text::class)->share(false);
        $this->container->register(Date::class)->share(false);
        $this->container->register(Password::class)->share(false);
        $this->container->register(Hidden::class)->share(false);
        $this->container->register(Button::class)->share(false);
        $this->container->register(Checkbox::class)->share(false);
        $this->container->register(Select::class)->share(false);
        $this->container->register(Textarea::class)->share(false);

        $this->router = $this->container->get(Router::class);
        $this->eventDispatcher = $this->container->get(EventDispatcher::class);
        $this->eventDispatcher->addSubscriber($this->container->get(SessionEventHandler::class));
        $this->eventDispatcher->addSubscriber($this->container->get(PerformanceHeadersResponseSubscriber::class));

        $this->initializeErrorHandler();

        $discovery = $this->container->get(CommandDiscovery::class);
        \assert($discovery instanceof CommandDiscovery);
        $discovery->loadBuiltInCommands();
    }

    #[Override]
    public function boot(): static
    {
        $this->booted = true;
        return $this;
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

    /**
     * @param Closure(Container): void $config
     *
     * @return $this
     */
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

    #[Override]
    public function handle(Request $request): Response
    {
        $this->requestHandleTime = \microtime(true);

        if ($this->booted === false) {
            throw new RuntimeException('kernel not booted');
        }

        $requestStack = $this->container->get(RequestStack::class);
        $requestStack->push($request);

        $this->eventDispatcher->dispatch(new KernelRequestEvent($request));
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
                        ->prepare()
                        ->handleRequest(...$resolver->resolv($callback, 'handleRequest'));
                }
            } catch (Throwable $e) {
                $response = $this->dispatchException($e);
            }
        } catch (NoRouteMatching) {
            $response = $this->build404Response();
        } catch (RouteGotFiltered $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = $this->build307Response();
            }
        }

        $this->responseTime = \microtime(true);
        $this->eventDispatcher->dispatch(new KernelResponseEvent($response));

        $requestStack->pop();
        return $response;
    }

    protected function dispatchException(Throwable $exception): Response
    {
        $stack = $this->container->get(RequestStack::class);
        if ($stack->hasRequest()) {
            $throwable = new ExceptionEvent($exception, $stack->getCurrentRequest());
        } else {
            $throwable = new ExceptionEvent($exception);
        }

        $exceptionEvent = $this->eventDispatcher->dispatch($throwable);

        if ($exceptionEvent->hasResponse()) {
            return $exceptionEvent->getResponse();
        }

        throw $exceptionEvent->getThrowable();
    }

    protected function initializeErrorHandler(): void
    {
        \set_error_handler(function ($errno, $errstr, $errfile, $errline): never {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        \set_exception_handler(function (Throwable $e): void {
            $this->dispatchException($e);
        });
    }

    protected function unregisterErrorHandler(): void
    {
        \restore_error_handler();
        \restore_exception_handler();
    }

    #[Override]
    public function execute(Console $cli): ExitCode
    {
        $exec = $this->container->get(CommandExecutor::class);
        \assert($exec instanceof CommandExecutor);

        return $exec->execute($cli);
    }

    public function loadCommands(string $path, string $pathPrefix, string $namespace): void
    {
        $discovery = $this->container->get(CommandDiscovery::class);
        \assert($discovery instanceof CommandDiscovery);

        $discovery->findCommands($path, $pathPrefix, $namespace);
        $discovery->loadCommands();
    }

    #[Override]
    public function shutdown(): void
    {
        $this->unregisterErrorHandler();
    }

    #[Override]
    public function getMetrics(): KernelMetricDto
    {
        return new KernelMetricDto(
            $this->constructTime,
            $this->requestHandleTime,
            $this->responseTime,
        );
    }
}
