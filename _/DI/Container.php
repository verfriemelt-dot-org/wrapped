<?php

    namespace verfriemelt\wrapped\_\DI;

    use \Exception;

    class Container {

        private array $services = [];

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
        }

        protected function build( string $class ): object {
            $arguments = (new ArgumentResolver( $this, new ArgumentMetadataFactory ) )->resolv( $class );
            return new $class( ... $arguments );
        }

        public function get( string $id ): object {
            return
                $this->services[$id] ??
                $this->make( $id ) ??
                throw new Exception( sprintf( 'service »%s« not found', $id ) );
        }

    }
