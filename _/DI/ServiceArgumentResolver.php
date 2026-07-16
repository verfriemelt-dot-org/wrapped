<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use Override;
use verfriemelt\wrapped\_\DI\Attributes\Env;
use verfriemelt\wrapped\_\DotEnv\Environment;
use verfriemelt\wrapped\_\DotEnv\EnvironmentNotFoundException;

/**
 * @template T of object
 */
class ServiceArgumentResolver extends ArgumentResolver
{
    /** @var ServiceConfiguration<T> */
    protected ServiceConfiguration $serviceConfiguration;

    /**
     * @param ServiceConfiguration<T> $serviceConfiguration
     */
    public function __construct(Container $container, ArgumentMetadataFactory $factory, ServiceConfiguration $serviceConfiguration)
    {
        parent::__construct($container, $factory);

        $this->serviceConfiguration = $serviceConfiguration;
    }

    #[Override]
    protected function buildParameter(ArgumentMetadata $parameter): mixed
    {
        foreach ([$parameter->getName(), ...$parameter->getTypes()] as $param) {
            $paramResolver = $this->serviceConfiguration->getResolver($param);

            if ($paramResolver === null) {
                continue;
            }

            return $paramResolver(
                ...$this->resolv($paramResolver),
            );
        }

        // we do not support union or intersection types
        if (count($parameter->getTypes()) === 1) {

            $type = $parameter->getTypes()[0];

            // if defined, use that
            if ($this->serviceConfiguration->hasParameter($parameter->getName())) {
                return $this->container->get($type);
            }

            // handle scalartypes without defaults
            if (\in_array($type, ['string', 'bool', 'int', 'float'], strict: true)) {
                return $this->buildScalarParameter($parameter);
            }

            // if not default but default, use that
            if ($parameter->hasDefaultValue()) {
                return $parameter->getDefaultValue();
            }

            // resolv via service configuration
            return $this->container->get($type);
        }

        throw new ArgumentResolverException("cannot resolv params for {$this->serviceConfiguration->getClass()}::{$parameter->getMethodName()}() \${$parameter->getName()}");
    }

    private function buildScalarParameter(ArgumentMetadata $parameter): mixed
    {

        $attribute = $parameter->findAttribute(Env::class);

        if ($attribute === null && $parameter->hasDefaultValue()) {
            return $parameter->getDefaultValue();
        }

        assert($attribute instanceof Env);

        $env = $this->container->get(Environment::class);

        $argumentValue = null;

        try {
            $argumentValue = match ($parameter->getTypes()[0]) {
                'string' =>  $env->string($attribute->name),
                'int' => $env->int($attribute->name),
                default => throw new ArgumentResolverException("cannot resolv params for {$this->serviceConfiguration->getClass()}::{$parameter->getMethodName()}() \${$parameter->getName()}"),
            };

        } catch (EnvironmentNotFoundException) {
            $argumentValue ??= $attribute->default ?? $parameter->getDefaultValue();
        }

        if ($argumentValue !== null) {
            return $argumentValue;
        }

        throw new ArgumentResolverException("cannot resolv params for {$this->serviceConfiguration->getClass()}::{$parameter->getMethodName()}() \${$parameter->getName()}");
    }
}
