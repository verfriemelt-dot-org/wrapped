<?php

    namespace verfriemelt\wrapped\_\DI;

    use \Closure;
    use \Exception;

    /**
     * @template T of object
     */
    class ServiceConfiguration {

        private bool $shareable = true;

        private string $id;

        /**
         * @var class-string<T>
         */
        private string $class;

        private $resolver = [];

        /**
         * @param class-string<T> $id
         */
        public function __construct( string $id ) {
            $this->id = $id;

            if ( class_exists($id) ) {
                $this->setClass( $id );
            }
        }

        public function share( bool $bool = true ): static {
            $this->shareable = $bool;
            return $this;
        }

        public function isShareable(): bool {
            return $this->shareable;
        }

        /**
         * @param class-string<T> $class
         * @throws Exception
         */
        public function setClass( string $class ): static {

            if ( !class_exists( $class ) ) {
                throw new Exception( sprintf( 'unkown class: »%s«', $class ) );
            }

            $this->class = $class;
            return $this;
        }

        /**
         * @return class-string<T>
         */
        public function getClass(): string {
            return $this->class;
        }

        /**
         * @return class-string[]
         */
        public function getInterfaces(): array {

            /** @var class-string[] $interfaces */
            $interfaces = class_implements( $this->class );

            return $interfaces;
        }

        /**
         *
         * @param class-string $class
         * @param Closure $resolver
         * @return static
         */
        public function parameter( string $class, Closure $resolver ): static {
            $this->resolver[$class] = $resolver;
            return $this;
        }

        /**
         *
         * @param class-string $class
         * @return bool
         */
        public function hasParameter( string $class ): bool {
            return isset( $this->resolver[$class] );
        }

        /**
         *
         * @param class-string $class
         * @return mixed
         */
        public function getParemeter( string $class ): mixed {
            return $this->resolver[$class];
        }

    }
