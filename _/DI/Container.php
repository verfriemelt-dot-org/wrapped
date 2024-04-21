<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use Closure;
use Exception;
use InvalidArgumentException;
use Override;
use ReflectionClass;
use ReflectionException;

class Container implements ContainerInterface
{
    private array $instances = [];

    /** @var array<class-string,int> */
    private array $interfacesCount = [];

    private array $currentlyLoading = [];

    /** @var class-string[][] */
    private array $tags = [];

    /** @var array<class-string,ServiceConfiguration> */
    private array $serviceConfigurations = [];

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->register(static::class, $this);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $service
     * @param T               $instance
     *
     * @return ServiceConfiguration<T>
     */
    #[Override]
    public function register(string $service, ?object $instance = null): ServiceConfiguration
    {
        try {
            $reflection = new ReflectionClass($instance ?? $service);
        } catch (ReflectionException $exception) {
            throw new ContainerException($exception->getMessage());
        }

        if ($reflection->isInterface() && $instance === null) {
            throw new InvalidArgumentException("{$service} interface must be registered with an instance");
        }

        $serviceConfiguration = $this->serviceConfigurations[$service] = (new ServiceConfiguration($service));

        if ($instance instanceof Closure) {
            $serviceConfiguration->factory($instance);
        } elseif ($instance !== null) {
            $this->instances[$service] = $instance;
        }

        if ($reflection->isInterface()) {
            $this->interfacesCount[$service] ??= 0;
            ++$this->interfacesCount[$service];
        }

        foreach ($serviceConfiguration->getInterfaces() as $interface) {
            if ($service === $interface) {
                continue;
            }
            $this->serviceConfigurations[$interface] ??= $serviceConfiguration;
            $this->interfacesCount[$interface] ??= 0;
            ++$this->interfacesCount[$interface];
        }

        return $serviceConfiguration;
    }

    /**
     * @param class-string $service
     */
    #[Override]
    public function has(string $service): bool
    {
        try {
            $reflection = new ReflectionClass($service);
        } catch (ReflectionException $exception) {
            throw new ContainerException($exception->getMessage());
        }

        return true;
    }

    /**
     * @template T of object
     *
     * @param ServiceConfiguration<T> $config
     *
     * @return T
     *
     * @throws Exception
     */
    private function build(ServiceConfiguration $config): object
    {
        if (in_array($config->getClass(), $this->currentlyLoading)) {
            throw new ContainerException("circulare references while loading {$config->getClass()}. Stack: \n" . print_r($this->currentlyLoading, true));
        }

        $this->currentlyLoading[] = $config->getClass();

        $builder = new ServiceBuilder($config, $this);
        $instance = $builder->build();

        array_pop($this->currentlyLoading);

        return $instance;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $service
     *
     * @return T
     *
     * @throws ContainerException
     */
    #[Override]
    public function get(string $service): object
    {
        if (\interface_exists($service)) {
            if (($this->interfacesCount[$service] ?? 0) > 1) {
                throw new ContainerException(\sprintf('multiple implementations preset for interface: »%s«', $service));
            }
        }

        $this->serviceConfigurations[$service] ??= $this->register($service);

        if ($this->serviceConfigurations[$service]->isShareable()) {
            return $this->instances[$this->serviceConfigurations[$service]->getClass()] ??= $this->build($this->serviceConfigurations[$service]);
        }

        return $this->build($this->serviceConfigurations[$service]);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $service
     * @param T               $instance
     *
     * @return ServiceConfiguration<T>
     */
    #[Override]
    public function replaceInterface(string $service, object $instance): ServiceConfiguration
    {
        if (!\interface_exists($service)) {
            throw new ContainerException('unknown interace' . $service);
        }

        unset($this->serviceConfigurations[$service]);
        return $this->register($service, $instance);
    }

    #[Override]
    public function tag(string $tag, string $class): void
    {
        $this->tags[$tag] ??= [];
        $this->tags[$tag][] = $class;
    }

    /**
     * @return iterable<class-string>
     */
    #[Override]
    public function tagIterator(string $tag): iterable
    {
        return $this->tags[$tag] ?? [];
    }
}
