<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

class ArgumentResolver
{
    protected Container $container;

    protected ArgumentMetadataFactory $factory;

    public function __construct(Container $container, ArgumentMetadataFactory $factory)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    public function resolv(object|string $obj, string $method = null): array
    {
        $args = [];

        foreach ($this->factory->createArgumentMetadata($obj, $method) as $parameter) {
            if ($parameter->hasDefaultValue()) {
                continue;
            }

            $args[] = $this->buildParameter($parameter);
        }

        return $args;
    }

    protected function buildParameter(ArgumentMetadata $parameter)
    {
        return $this->container->get($parameter->getType());
    }
}
