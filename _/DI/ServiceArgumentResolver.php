<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

    use Closure;

    /**
     * @template T of object
     */
    class ServiceArgumentResolver extends ArgumentResolver
    {
        /**
         * @var ServiceConfiguration<T>
         */
        protected ServiceConfiguration $service;

        /**
         * @param ServiceConfiguration<T> $service
         */
        public function __construct(Container $container, ArgumentMetadataFactory $factory, ServiceConfiguration $service)
        {
            parent::__construct($container, $factory);

            $this->service = $service;
        }

        protected function buildParameter(ArgumentMetadata $parameter)
        {
            if ($parameter->hasType() && $this->service->hasParameter($parameter->getType())) {
                $param = $this->service->getParemeter($parameter->getType());

                if ($param instanceof Closure) {
                    return $param(...(new ArgumentResolver($this->container, new ArgumentMetadataFactory()) )->resolv($param));
                }

                return $param;
            }

            return $this->container->get($parameter->getType());
        }
    }
