<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DI;

    use \Exception;

    class Container {

        /** @var ServiceConfiguration<object>[] */
        private array $services = [];

        private array $instances = [];

        /** @var array<string,ServiceConfiguration<object>[]> */
        private array $interfaces = [];

        private array $currentlyLoading = [];

        public function __construct() {
            /** @phpstan-ignore-next-line */
            $this->register( static::class, $this );
        }

        /**
         * @template T of object
         * @param class-string<T> $id
         * @param T $instance
         * @return ServiceConfiguration<T>
         */
        public function register( string $id, object $instance = null ): ServiceConfiguration {

            /** @var ServiceConfiguration<T> $service */
            $service = (new ServiceConfiguration( $id ) );

            if ( $instance !== null ) {
                $this->instances[$id] = $instance;
                $service->setClass( get_class( $instance ) );
            }

            foreach ( $service->getInterfaces() as $interface ) {

                $this->interfaces[$interface]   ??= [];
                $this->interfaces[$interface][] = $service;
            }

            $this->services[$id] = $service;

            return $service;
        }

        /**
         *
         * @param class-string $id
         * @return bool
         */
        public function has( string $id ): bool {
            return isset( $this->services[$id] ) || $this->generateDefaultService( $id );
        }

        /**
         * @template T of object
         * @param class-string<T> $id
         * @return bool
         */
        public function generateDefaultService( string $id ): bool {
            /** @phpstan-ignore-next-line */
            $this->register( $id, null );
            return true;
        }

        /**
         * @template T of object
         * @param ServiceConfiguration<T> $config
         * @return T
         * @throws Exception
         */
        private function build( ServiceConfiguration $config ): object {

            if ( in_array( $config->getClass(), $this->currentlyLoading ) ) {
                throw new Exception( sprintf( 'circulare references' ) );
            }

            $this->currentlyLoading[] = $config->getClass();

            $builder  = new ServiceBuilder( $config, $this );
            $instance = $builder->build();

            array_pop( $this->currentlyLoading );

            return $instance;
        }

        /**
         * @template T of object
         * @param class-string<T> $id
         * @return T
         * @throws Exception
         */
        public function get( string $id ): object {

            if ( $id === '' ) {
                throw new ContainerException('illegal class');
            }

            if ( interface_exists( $id ) ) {
                $configuration = $this->getInterface( $id );
            } else {

                if ( !$this->has( $id ) ) {
                    throw new Exception( sprintf( 'unkown service: »%s«', $id ) );
                }

                $configuration = $this->services[$id];
            }

            if ( !$configuration->isShareable() ) {
                /** @phpstan-ignore-next-line */
                return $this->build( $configuration );
            }

            if ( !isset( $this->instances[$id] ) ) {
                $this->instances[$id] = $this->build( $configuration );
            }

            return $this->instances[$id];
        }

        /**
         * @template T of object
         * @param class-string<T> $class
         * @return ServiceConfiguration<T>
         * @throws Exception
         */
        private function getInterface( string $class ): ServiceConfiguration {

            if ( !isset( $this->interfaces[$class] ) ) {
                throw new Exception( sprintf( 'unkown interface: »%s«', $class ) );
            }

            if ( count( $this->interfaces[$class] ) > 1 ) {
                throw new Exception( sprintf( 'multiple implementations preset for interface: »%s«', $class ) );
            }

            /** @phpstan-ignore-next-line */
            return $this->interfaces[$class][0];
        }

    }
