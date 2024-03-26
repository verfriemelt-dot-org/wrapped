<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use Closure;
use Exception;

class Container
{
    /** @var ServiceConfiguration<object>[] */
    private array $services = [];

    private array $instances = [];

    /** @var array<string,ServiceConfiguration[]> */
    private array $interfaces = [];

    private array $currentlyLoading = [];

    /** @var class-string[][] */
    private array $tags = [];

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->register(static::class, $this);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param T               $instance
     *
     * @return ServiceConfiguration<T>
     */
    public function register(string $class, ?object $instance = null): ServiceConfiguration
    {
        if (!\class_exists($class) && !\interface_exists($class)) {
            throw new ContainerException("class or interface «{$class}» not found");
        }

        /** @var ServiceConfiguration<T> $service */
        $service = (new ServiceConfiguration($class));

        if ($instance instanceof Closure) {
            $service->factory($instance);
            $instance = null;
        }

        if ($instance !== null) {
            $this->instances[$class] = $instance;
            $service->setClass($instance::class);
        }

        foreach ($service->getInterfaces() as $interface) {
            $this->interfaces[$interface] ??= [];
            $this->interfaces[$interface][] = $service;
        }

        // tag interfaces by default
        if (\interface_exists($class) && $instance !== null) {
            $this->tag($class, $instance::class);
        }

        $this->services[$class] = $service;

        return $service;
    }

    /**
     * @param class-string $id
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) || $this->generateDefaultService($id);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     */
    public function generateDefaultService(string $id): bool
    {
        if (\class_exists($id)) {
            /* @phpstan-ignore-next-line */
            $this->register($id, null);
            return true;
        }

        return false;
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
     * @param class-string<T> $id
     *
     * @return T
     *
     * @throws ContainerException
     */
    public function get(string $id): object
    {
        if ($id === '') {
            throw new ContainerException('illegal class');
        }

        if (\interface_exists($id)) {
            $configuration = $this->getInterface($id);
        } else {
            if (!$this->has($id)) {
                throw new ContainerException(sprintf('unkown service: »%s«', $id));
            }

            $configuration = $this->services[$id];
        }

        if (!$configuration->isShareable()) {
            /* @phpstan-ignore-next-line */
            return $this->build($configuration);
        }

        return $this->instances[$id] ??= $this->build($configuration);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return ServiceConfiguration<T>
     *
     * @throws ContainerException
     */
    private function getInterface(string $class): ServiceConfiguration
    {
        if (!isset($this->interfaces[$class])) {
            throw new ContainerException(sprintf('unkown interface: »%s«', $class));
        }

        if (count($this->interfaces[$class]) > 1) {
            throw new ContainerException(sprintf('multiple implementations preset for interface: »%s«', $class));
        }

        assert(\array_is_list($this->interfaces[$class]));

        return $this->interfaces[$class][0];
    }

    public function resetInterface(string $class): void
    {
        unset($this->interfaces[$class]);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param T               $instance
     *
     * @return ServiceConfiguration<T>
     */
    public function replaceInterace(string $class, object $instance): ServiceConfiguration
    {
        $this->resetInterface($class);
        return $this->register($class, $instance);
    }

    public function tag(string $tag, string $class): void
    {
        $this->tags[$tag] ??= [];
        $this->tags[$tag][] = $class;
    }

    /**
     * @return iterable<class-string>
     */
    public function tagIterator(string $tag): iterable
    {
        return $this->tags[$tag] ?? [];
    }
}
