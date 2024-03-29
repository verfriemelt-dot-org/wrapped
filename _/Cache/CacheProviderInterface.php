<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cache;

interface CacheProviderInterface
{
    public function replace(string $key, $value, int $timeout = 0): bool;

    public function set(string $key, $value, int $timeout = 0): bool;

    public function get(string $key);

    public function delete(string $key): bool;

    public function purge();
}
