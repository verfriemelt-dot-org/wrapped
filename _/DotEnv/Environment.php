<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DotEnv;

use RuntimeException;

final class Environment
{
    public function string(string $name, ?string $default = null): string
    {
        return $this->getScalar($name) ?? $default ?? throw new RuntimeException("_ENV['{$name}'] not defined");
    }

    private function getScalar(string $name): ?string
    {
        if (!isset($_ENV[$name])) {
            return null;
        }

        if (!\is_scalar($_ENV[$name])) {
            throw new RuntimeException("_ENV['{$name}'] is not a scalar");
        }

        return (string) $_ENV[$name];
    }
}
