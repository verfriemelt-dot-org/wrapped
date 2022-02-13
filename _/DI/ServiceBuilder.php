<?php

    namespace verfriemelt\wrapped\_\DI;

    /**
     * @template T of object
     */
    class ServiceBuilder {

        /**
         *
         * @var ServiceConfiguration<T>
         */
        private ServiceConfiguration $service;

        private Container $container;


        /**
         *
         * @param ServiceConfiguration<T> $service
         * @param Container $container
         */
        public function __construct( ServiceConfiguration $service, Container $container ) {
            $this->service   = $service;
            $this->container = $container;
        }

        /**
         * @return T
         */
        public function build() {

            $class = $this->service->getClass();

            $arguments = (new ServiceArgumentResolver( $this->container, new ArgumentMetadataFactory, $this->service ) )
                ->resolv( $class );

            return new $class( ... $arguments );
        }

    }
