<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use Override;

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

    #[Override]
    protected function buildParameter(ArgumentMetadata $parameter): mixed
    {
        foreach ([$parameter->getName(), ...$parameter->getTypes()] as $param) {
            $paramResolver = $this->service->getResolver($param);

            if ($paramResolver === null) {
                continue;
            }

            return $paramResolver(
                ...$this->resolv($paramResolver),
            );
        }

        if (count($parameter->getTypes()) === 1) {
            if (!$this->service->hasParameter($parameter->getName()) && $parameter->hasDefaultValue()) {
                return $parameter->getDefaultValue();
            }

            return $this->container->get($parameter->getTypes()[0]);
        }

        throw new ArgumentResolverException("cannot resolv params for {$this->service->getClass()}::{$parameter->getMethodName()}() \${$parameter->getName()}");
    }
}
