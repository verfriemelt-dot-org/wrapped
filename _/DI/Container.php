<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DI;

    use \Exception;

    class Container {

        /** @var verfriemelt\wrapped\_\DI\ServiceConfiguration[] */
        private array $services = [];

        private array $instances = [];

        private array $currentlyLoading = [];

        public function __construct() {
            $this->register( static::class, $this );
        }

        public function register( string $id, object $service = null ): ServiceConfiguration {

            $this->services[$id] = (new ServiceConfiguration( $id ) );

            if ( $service !== null ) {
                $this->instances[$id] = $service;
                $this->services[$id]->class( get_class( $service ) );
            }

            return $this->services[$id];
        }

        public function has( string $id ): bool {
            return isset( $this->services[$id] ) || $this->generateDefaultService( $id );
        }

        public function generateDefaultService( string $id ): bool {
            $this->register( $id, null );
            return true;
        }

        private function build( string $id ): object {

            if ( in_array( $id, $this->currentlyLoading ) ) {
                throw new Exception( sprintf( 'circulare references' ) );
            }

            $this->currentlyLoading[] = $id;

            $builder  = new ServiceBuilder( $this->services[$id], $this );
            $instance = $builder->build();

            array_pop( $this->currentlyLoading );

            return $instance;
        }

        public function get( string $id ): object {

            if ( !$this->has( $id ) ) {
                throw new Exception( sprintf( 'unkown service: »%s«', $id ) );
            }

            $configuration = $this->services[$id];

            if ( !$configuration->isShareable() ) {
                return $this->build( $id );
            }

            if ( !isset( $this->instances[$id] ) ) {
                $this->instances[$id] = $this->build( $id );
            }

            return $this->instances[$id];
        }

    }
