<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cache;

    class MemcachedProvider implements CacheProviderInterface
    {
        private \Memcached $memcached;

        private static string $prefix = '';

        public function __construct($server = '127.0.0.1', $port = 11211)
        {
            $this->memcached = new \Memcached();
            $this->memcached->addServer($server, $port);
        }

        public static function isAvailable(): bool
        {
            return class_exists('\\Memcached');
        }

        public static function setPrefix(string $prefix)
        {
            static::$prefix = $prefix;
        }

        private function prefix(string $key)
        {
            return static::$prefix . $key;
        }

        public function set(string $key, $value, int $timeout = 0): bool
        {
            return $this->memcached->set($this->prefix($key), $value, $timeout);
        }

        public function delete(string $key): bool
        {
            return $this->memcached->delete($this->prefix($key));
        }

        /**
         * returns false on not found key
         *
         * @return type
         */
        public function get(string $key)
        {
            return $this->memcached->get($this->prefix($key));
        }

        public function purge()
        {
            // nope
        }

        public function replace(string $key, $value, int $timeout = 0): bool
        {
            return $this->memcached->replace($this->prefix($key), $value, $timeout);
        }
    }
