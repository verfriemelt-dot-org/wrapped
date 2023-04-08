<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cache;

class Cache
{
    private readonly \verfriemelt\wrapped\_\Cache\CacheProviderInterface $provider;

    public function __construct(CacheProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function set(string $key, $value, int $timeout = 0): bool
    {
        return $this->provider->set($key, $value, $timeout);
    }

    /**
     * false on not existing key
     *
     * @return type
     */
    public function get(string $key)
    {
        return $this->provider->get($key);
    }

    public function delete(string $key): bool
    {
        return $this->provider->delete($key);
    }

    public function replace(string $key, $value, int $timeout = 0): bool
    {
        return $this->replace($key, $value, $timeout);
    }
}
