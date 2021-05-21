<?php

    namespace verfriemelt\wrapped\_\DI;

    class ArgumentResolver {

        private $reflection;

        protected Container $container;

        protected ArgumentMetadataFactory $factory;

        public function __construct( Container $container, ArgumentMetadataFactory $factory ) {

            $this->factory   = $factory;
            $this->container = $container;
        }

        public function resolv( object|string $obj, string $method = null ): array {

            $args = [];

            foreach ( $this->factory->createArgumentMetadata( $obj, $method ) as $parameter ) {

                if ( $parameter->hasDefaultValue() ) {
                    continue;
                }

                $args[] = $this->container->get( $parameter->getType() );
            }

            return $args;
        }

    }
