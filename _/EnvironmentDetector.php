<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_;

use InvalidArgumentException;

class EnvironmentDetector
{
    private array $detector = [];

    /**
     * @param string $envName
     *
     * @throws InvalidArgumentException
     */
    public function registerDetector($envName, callable $func)
    {
        if (!is_callable($func)) {
            throw new InvalidArgumentException('Illegal Detector');
        }

        $this->detector[$envName] = $func;
    }

    /**
     * @param string $envName
     *
     * @return bool
     */
    public function is($envName)
    {
        if (isset($this->detector[$envName])) {
            $func = $this->detector[$envName];

            return $func();
        }

        return false;
    }

    /**
     * @param string $envName
     *
     * @return bool
     */
    public function isNot($envName)
    {
        return !$this->is($envName);
    }

    /**
     * for debug only
     *
     * @return mixed
     */
    public function dumpDetectorsResults()
    {
        $detectors = [];
        foreach ($this->detector as $name => $func) {
            $detectors[$name] = $func();
        }

        return $detectors;
    }
}
