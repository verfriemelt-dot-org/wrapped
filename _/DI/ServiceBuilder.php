<?php

    namespace verfriemelt\wrapped\_\DI;

    class ServiceBuilder {

        private ServiceConfiguration $service;

        private Container $container;

        public function __construct( ServiceConfiguration $service, Container $container ) {
            $this->service   = $service;
            $this->container = $container;
        }

        public function build() {

            $class = $this->service->getClass();

            $arguments = (new ServiceArgumentResolver( $this->container, new ArgumentMetadataFactory, $this->service ) )
                ->resolv( $class );

            return new $class( ... $arguments );
        }

    }
