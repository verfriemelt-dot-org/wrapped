<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DotEnv;

use http\Exception\RuntimeException;

final class DotEnv
{
    private const STORAGE_KEY = '_WRAPPED_HANDLED_VARS';

    public function load(string ...$paths): self
    {
        foreach ($paths as $path) {
            if (!file_exists($path) || !is_readable($path)) {
                throw new \RuntimeException("cannot load {$path}");
            }

            $this->putenv($this->parseFile($path));
        }

        return $this;
    }

    /**
     * @return array<string,string>
     */
    private function parseFile(string $path): array
    {
        $env = parse_ini_file($path, false, INI_SCANNER_RAW);

        if ($env === false) {
            throw new RuntimeException('broken dotenv');
        }

        return $env;
    }

    /** @param array<string,string> $env */
    private function putenv(array $env): void
    {
        $getenv = getenv(static::STORAGE_KEY);

        if ($getenv === false) {
            $handledVars = [];
        } else {
            $handledVars = \explode(',', $getenv);
        }

        foreach ($env as $key => $value) {
            if (getenv($key) === false) {
                $handledVars[] = $key;
                $_ENV[$key] = $value;
                \putenv("{$key}={$value}");

                continue;
            }

            if (\in_array($key, $handledVars, true)) {
                $_ENV[$key] = $value;
                \putenv("{$key}={$value}");
            }

            $_ENV[$key] = getenv($key);
        }

        $_ENV[static::STORAGE_KEY] = \implode(',', $handledVars);
        \putenv(sprintf('%s=%s', static::STORAGE_KEY, \implode(',', $handledVars)));
    }
}
