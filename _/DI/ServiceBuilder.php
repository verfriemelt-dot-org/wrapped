<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

/**
 * @template T of object
 */
class ServiceBuilder
{
    /** @var ServiceConfiguration<T> */
    private readonly ServiceConfiguration $service;

    private readonly Container $container;

    /**
     * @param ServiceConfiguration<T> $service
     */
    public function __construct(ServiceConfiguration $service, Container $container)
    {
        $this->service = $service;
        $this->container = $container;
    }

    /**
     * @return T
     */
    public function build(): object
    {
        $class = $this->service->getClass();

        if ($this->service->hasFactory()) {
            return $this->callFactory();
        }

        $arguments = (new ServiceArgumentResolver($this->container, new ArgumentMetadataFactory(), $this->service))
            ->resolv($class);

        return new $class(...$arguments);
    }

    /**
     * @return T
     */
    private function callFactory(): object
    {
        $factory = $this->service->getFactory();

        $arguments = (new ArgumentResolver($this->container, new ArgumentMetadataFactory()))
            ->resolv($factory);

        return $factory(... $arguments);
    }
}
