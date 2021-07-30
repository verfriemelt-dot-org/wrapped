<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DI;

    use \Exception;

    class Container {

        /** @var verfriemelt\wrapped\_\DI\ServiceConfiguration[] */
        private array $services = [];

        private array $instances = [];

        /** @var verfriemelt\wrapped\_\DI\ServiceConfiguration[] */
        private array $interfaces = [];

        private array $currentlyLoading = [];

        public function __construct() {
            $this->register( static::class, $this );
        }

        public function register( string $id, object $instance = null ): ServiceConfiguration {

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

        public function has( string $id ): bool {
            return isset( $this->services[$id] ) || $this->generateDefaultService( $id );
        }

        public function generateDefaultService( string $id ): bool {
            $this->register( $id, null );
            return true;
        }

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

        public function get( string $id ): object {

            if ( interface_exists( $id ) ) {
                $configuration = $this->getInterface( $id );
            } else {

                if ( !$this->has( $id ) ) {
                    throw new Exception( sprintf( 'unkown service: »%s«', $id ) );
                }

                $configuration = $this->services[$id];
            }

            if ( !$configuration->isShareable() ) {
                return $this->build( $configuration );
            }

            if ( !isset( $this->instances[$id] ) ) {
                $this->instances[$id] = $this->build( $configuration );
            }

            return $this->instances[$id];
        }

        private function getInterface( string $class ): ServiceConfiguration {

            if ( !isset( $this->interfaces[$class] ) ) {
                throw new Exception( sprintf( 'unkown interface: »%s«', $class ) );
            }

            if ( count( $this->interfaces[$class] ) > 1 ) {
                throw new Exception( sprintf( 'multiple implementations preset for interface: »%s«', $class ) );
            }

            return $this->interfaces[$class][0];
        }

    }
