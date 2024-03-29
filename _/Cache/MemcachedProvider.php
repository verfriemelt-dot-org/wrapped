<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cache;

use Memcached;
use Override;

class MemcachedProvider implements CacheProviderInterface
{
    private readonly Memcached $memcached;

    private static string $prefix = '';

    public function __construct($server = '127.0.0.1', $port = 11211)
    {
        $this->memcached = new Memcached();
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

    #[Override]
    public function set(string $key, $value, int $timeout = 0): bool
    {
        return $this->memcached->set($this->prefix($key), $value, $timeout);
    }

    #[Override]
    public function delete(string $key): bool
    {
        return $this->memcached->delete($this->prefix($key));
    }

    /**
     * returns false on not found key
     *
     * @return type
     */
    #[Override]
    public function get(string $key)
    {
        return $this->memcached->get($this->prefix($key));
    }

    #[Override]
    public function purge()
    {
        // nope
    }

    #[Override]
    public function replace(string $key, $value, int $timeout = 0): bool
    {
        return $this->memcached->replace($this->prefix($key), $value, $timeout);
    }
}
