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

    /**
     * @param object|class-string $obj
     */
    public function resolv(object|string $obj, ?string $method = null, int $skip = 0): array
    {
        $args = [];
        $count = 0;
        foreach ($this->factory->createArgumentMetadata($obj, $method) as $parameter) {
            if ($count++ < $skip) {
                continue;
            }

            $args[] = $this->buildParameter($parameter);
        }

        return $args;
    }

    protected function buildParameter(ArgumentMetadata $parameter): mixed
    {
        $types = $parameter->getTypes();

        if (count($types) !== 1) {
            throw new RuntimeException('cannot resolv union type');
        }

        $type = $types[0];

        if (!\class_exists($type) && $parameter->hasDefaultValue()) {
            return $parameter->getDefaultValue();
        }

        return $this->container->get($type);
    }
}
