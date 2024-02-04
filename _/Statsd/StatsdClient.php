<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Statsd;

use verfriemelt\wrapped\_\Singleton;

final class StatsdClient
{
    use Singleton;

    public const string COUNTER = 'c';

    public const string TIMER_MS = 'ms';

    public const string GAUGE = 'g';

    private ?Connection $connection = null;

    private string $namespace = '';

    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function incrementCounter(string $key): static
    {
        $this->counter($key, 1);
        return $this;
    }

    public function gauge(string $key, float $value): static
    {
        $this->send($key, $value, self::GAUGE);
        return $this;
    }

    public function decrementCounter(string $key): static
    {
        $this->counter($key, -1);
        return $this;
    }

    public function counter(string $key, int $value): static
    {
        $this->send($key, $value, self::COUNTER);
        return $this;
    }

    public function time(string $key, callable $function): void
    {
        $timer = new StatsdTimer($this, $key);
        $function();
        $timer->report();
    }

    public function createTimer(string $key): StatsdTimer
    {
        return new StatsdTimer($this, $key);
    }

    public function send(string $key, int|float $value, string $type, ?float $rate = null): static
    {
        $key = ($this->namespace !== '') ? "{$this->namespace}.{$key}" : $key;

        if ($rate !== null) {
            $message = sprintf('%s:%s|%s|@%0.1f', $key, $value, $type, $rate);
        } else {
            $message = sprintf('%s:%s|%s', $key, $value, $type);
        }

        if ($this->connection) {
            $this->connection->send($message);
        }

        return $this;
    }
}
