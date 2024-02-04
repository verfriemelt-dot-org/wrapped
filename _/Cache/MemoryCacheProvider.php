<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cache;

use Override;

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

    #[Override]
    public function set(string $key, $value, int $timeout = 0): bool
    {
        static::$cache[$key] = $value;
        return true;
    }

    #[Override]
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
    #[Override]
    public function get(string $key)
    {
        return static::$cache[$key] ?? false;
    }

    #[Override]
    public function purge()
    {
        static::$cache = [];
    }

    #[Override]
    public function replace(string $key, $value, int $timeout = 0): bool
    {
        return static::$cache[$key] = $value;
    }
}
