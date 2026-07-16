<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DotEnv;

final class Environment
{
    public function string(string $name, ?string $default = null): string
    {
        return $this->getScalar($name) ?? $default ?? throw new EnvironmentNotFoundException("_ENV['{$name}'] not defined");
    }

    public function int(string $name, ?int $default = null): int
    {
        $value = $this->getScalar($name) ?? $default ?? throw new EnvironmentNotFoundException("_ENV['{$name}'] not defined");

        if (!\is_numeric($value)) {
            throw new EnvironmentNotFoundException("_ENV['{$name}'] value is not an integer");
        }

        return (int) $value;
    }

    private function getScalar(string $name): ?string
    {
        if (!isset($_ENV[$name])) {
            return null;
        }

        if (!\is_scalar($_ENV[$name])) {
            throw new EnvironmentNotFoundException("_ENV['{$name}'] is not a scalar");
        }

        return (string) $_ENV[$name];
    }
}
