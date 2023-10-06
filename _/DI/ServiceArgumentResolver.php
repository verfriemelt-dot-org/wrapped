<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use RuntimeException;

/**
 * @template T of object
 */
class ServiceArgumentResolver extends ArgumentResolver
{
    /** @var ServiceConfiguration<T> */
    protected ServiceConfiguration $service;

    /**
     * @param ServiceConfiguration<T> $service
     */
    public function __construct(Container $container, ArgumentMetadataFactory $factory, ServiceConfiguration $service)
    {
        parent::__construct($container, $factory);

        $this->service = $service;
    }

    protected function buildParameter(ArgumentMetadata $parameter): object
    {
        foreach ([$parameter->getName(), ...$parameter->getTypes()] as $param) {
            if (!$this->service->hasParameter($param)) {
                continue;
            }

            $paramResolver = $this->service->getResolver($param);

            return $paramResolver(
                ...(new ArgumentResolver($this->container, new ArgumentMetadataFactory()))->resolv($param)
            );
        }

        $types = $parameter->getTypes();
        if (count($types) === 1) {
            return $this->container->get($types[0]);
        }

        throw new RuntimeException('cannot resolv params for ' . $parameter->getName());
    }
}
