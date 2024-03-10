<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Statsd;

final class StatsdClient
{
    public const string COUNTER = 'c';

    public const string TIMER_MS = 'ms';

    public const string GAUGE = 'g';

    private ?Connection $connection = null;

    private string $namespace = '';

    protected static StatsdClient $handle;

    private function __construct() {}

    final public static function getInstance(): self
    {
        if (!isset(self::$handle)) {
            self::$handle = new self();
        }

        return self::$handle;
    }

    public function setConnection(Connection $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function incrementCounter(string $key): self
    {
        $this->counter($key, 1);
        return $this;
    }

    public function gauge(string $key, float $value): self
    {
        $this->send($key, $value, self::GAUGE);
        return $this;
    }

    public function decrementCounter(string $key): self
    {
        $this->counter($key, -1);
        return $this;
    }

    public function counter(string $key, int $value): self
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

    public function send(string $key, int|float $value, string $type, ?float $rate = null): self
    {
        $key = ($this->namespace !== '') ? "{$this->namespace}.{$key}" : $key;

        if ($rate !== null) {
            $message = sprintf('%s:%s|%s|@%0.1f', $key, $value, $type, $rate);
        } else {
            $message = sprintf('%s:%s|%s', $key, $value, $type);
        }

        $this->connection?->send($message);

        return $this;
    }
}
