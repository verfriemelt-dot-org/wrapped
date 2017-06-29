<?php

    namespace Wrapped\_\Cache;

    use \core\Cache\CacheProviderInterface;

    class Cache {

        private $provider;

        public function __construct( CacheProviderInterface $provider ) {
            $this->provider = $provider;
        }

        public function set( string $key, $value, int $timeout = 0 ): bool {
            return $this->provider->set( $key, $value, $timeout );
        }

        public function get( string $key ) {
            return $this->provider->get( $key );
        }

        public function delete( string $key ): bool {
            return $this->provider->delete( $key );
        }

        public function replace( string $key, $value, int $timeout = 0 ): bool {
            return $this->replace( $key, $value, $timeout );
        }

    }
