<?php

    namespace Wrapped\_\Cache;

    use \Wrapped\_\Cache\Cache;
    use \Wrapped\_\Cache\CacheProviderInterface;

    class CacheFactory {

        private static $cacheProvider;

        public static function registerCachingProvider( CacheProviderInterface $provider ) {
            static::$cacheProvider = $provider;
        }

        public static function hasCache(): bool {
            return static::$cacheProvider !== null;
        }

        public static function getCache(): Cache {
            return new Cache( static::$cacheProvider );
        }

    }
