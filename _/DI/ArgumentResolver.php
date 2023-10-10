<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use RuntimeException;

class ArgumentResolver
{
    protected Container $container;

    protected ArgumentMetadataFactory $factory;

    public function __construct(Container $container, ArgumentMetadataFactory $factory)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    public function resolv(object|string $obj, ?string $method = null, int $skip = 0): array
    {
        $args = [];
        $count = 0;
        foreach ($this->factory->createArgumentMetadata($obj, $method) as $parameter) {
            if ($count++ < $skip) {
                continue;
            }

            if ($parameter->hasDefaultValue()) {
                continue;
            }

            $args[] = $this->buildParameter($parameter);
        }

        return $args;
    }

    protected function buildParameter(ArgumentMetadata $parameter): object
    {
        $types = $parameter->getTypes();

        if (count($types) !== 1) {
            throw new RuntimeException('cannot resolv union type');
        }

        return $this->container->get($types[0]);
    }
}
