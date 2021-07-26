<?php

    namespace verfriemelt\wrapped\_\DI;

    use \Closure;
    use \TheSeer\Tokenizer\Exception;

    class ServiceConfiguration {

        private bool $shareable = true;

        private string $id;

        private string $class;

        private $resolver = [];

        public function __construct( string $id ) {
            $this->id    = $id;
            $this->class = $id;
        }

        public function share( bool $bool = true ): static {
            $this->shareable = $bool;
            return $this;
        }

        public function isShareable(): bool {
            return $this->shareable;
        }

        public function class( string $class ): static {

            if ( !class_exists( $class ) ) {
                throw new Exception( sprintf( 'unkown service: »%s«', $class ) );
            }

            $this->class = $class;
            return $this;
        }

        public function getClass() {
            return $this->class;
        }

        public function parameter( string $class, Closure $resolver ): static {
            $this->resolver[$class] = $resolver;
            return $this;
        }

        public function hasParameter( string $class ): bool {
            return isset( $this->resolver[$class] );
        }

        public function getParemter( string $class ) {
            return $this->resolver[$class];
        }

    }
