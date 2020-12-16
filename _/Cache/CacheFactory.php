<?php

    namespace Wrapped\_\Cache;

    use \Wrapped\_\Cache\Cache;
    use \Wrapped\_\Cache\CacheProviderInterface;

    class CacheFactory {

        private static CacheProviderInterface $cacheProvider;

        public static function registerCachingProvider( CacheProviderInterface $provider ) {
            static::$cacheProvider = $provider;
        }

        public static function hasCache(): bool {
            return isset( static::$cacheProvider );
        }

        public static function getCache(): Cache {
            return new Cache( static::$cacheProvider );
        }

    }
