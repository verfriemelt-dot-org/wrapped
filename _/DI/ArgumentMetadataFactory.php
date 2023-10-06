<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use RuntimeException;

class ArgumentMetadataFactory
{
    public function createArgumentMetadata(object|string $obj, string $method = null): array
    {
        $reflection = null;
        $arguments = [];

        if ($obj instanceof Closure) {
            $reflection = new ReflectionFunction($obj);
        } elseif (is_object($obj) || class_exists($obj)) {
            $constructor = (new ReflectionClass($obj))->getConstructor();

            // no constructor defined
            if ($method === null && $constructor === null) {
                return [];
            }

            $reflection = new ReflectionMethod($obj, $method ?? $constructor->getName());
        } else {
            return [];
        }

        foreach ($reflection->getParameters() as $param) {
            $arguments[] = new ArgumentMetadata(
                $param->getName(),
                $this->getTypes($param, $reflection),
                $param->isDefaultValueAvailable(),
                $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
            );
        }

        return $arguments;
    }

    /**
     * @return array<class-string|string>
     */
    private function getTypes(ReflectionParameter $parameter, ReflectionFunctionAbstract $function): array
    {
        $type = $parameter->getType();

        if ($type === null) {
            return [];
        }

        if ($type instanceof ReflectionNamedType) {
            return [$type->getName()];
        }

        if ($type instanceof ReflectionUnionType) {
            return array_map(static fn (ReflectionNamedType $type): string => $type->getName(), $type->getTypes());
        }

        throw new RuntimeException('cannot get type');
    }
}
