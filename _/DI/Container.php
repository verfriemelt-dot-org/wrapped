<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DI;

    use \Exception;

    class Container {

        private array $services = [];

        private array $currentlyLoading = [];

        private array $unsharerable = [];

        public function __construct() {
            $this->register( static::class, $this );
        }

        public function markServiceNonShareable( string $id ): static {
            $this->unsharerable[$id] = true;
            return $this;
        }

        public function register( string $id, object | callable $service ): static {
            $this->services[$id] = $service;
            return $this;
        }

        public function has( string $id ): bool {

            return $this->services[$id] ??
                $this->make( $id );
        }

        public function make( string $id ) {

            if ( class_exists( $id ) ) {
                $instance = $this->build( $id );
                $this->register( $id, $instance );
                return $instance;
            }

            throw new Exception( sprintf( 'unkown service: »%s«', $id ) );
        }

        public function build( string $class ): object {

            if ( in_array( $class, $this->currentlyLoading ) ) {
                throw new Exception( sprintf( 'circulare references' ) );
            }

            $this->currentlyLoading[] = $class;

            $arguments = (new ArgumentResolver( $this, new ArgumentMetadataFactory ) )->resolv( $class );

            array_pop( $this->currentlyLoading );

            return new $class( ... $arguments );
        }

        public function get( string $id ): object {

            if ( isset( $this->unsharerable[$id] ) ) {

                return $this->make( $id ) ??
                    throw new Exception( sprintf( 'unkown service: »%s«', $id ) );
            }

            return
                $this->services[$id] ??
                $this->make( $id ) ??
                throw new Exception( sprintf( 'unkown service: »%s«', $id ) );
        }

    }
