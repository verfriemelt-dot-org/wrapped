<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cache;

class MemoryCacheProvider implements CacheProviderInterface
{
    public static $cache = [];

    public static function isAvailable(): bool
    {
        return true;
    }

    public static function setPrefix(string $prefix)
    {
        // nop
    }

    private function prefix(string $key)
    {
        return $key;
    }

    public function set(string $key, $value, int $timeout = 0): bool
    {
        static::$cache[$key] = $value;
        return true;
    }

    public function delete(string $key): bool
    {
        unset(static::$cache[$key]);
        return true;
    }

    /**
     * returns false on not found key
     *
     * @return type
     */
    public function get(string $key)
    {
        return static::$cache[$key] ?? false;
    }

    public function purge()
    {
        static::$cache = [];
    }

    public function replace(string $key, $value, int $timeout = 0): bool
    {
        return static::$cache[$key] = $value;
    }
}
