<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Statsd;

final class StatsdClient
{
    use \verfriemelt\wrapped\_\Singleton;

    public const COUNTER = 'c';

    public const TIMER_MS = 'ms';

    public const GAUGE = 'g';

    private ?\verfriemelt\wrapped\_\Statsd\Connection $connection = null;

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

    /**
     * @param type $key
     *
     * @return \verfriemelt\wrapped\_\Statsd\StatsdClient
     */
    public function incrementCounter($key)
    {
        $this->counter($key, 1);
        return $this;
    }

    /**
     * @param type $key
     * @param type $value
     *
     * @return \verfriemelt\wrapped\_\Statsd\StatsdClient
     */
    public function gauge($key, $value)
    {
        $this->send($key, $value, self::GAUGE);
        return $this;
    }

    /**
     * @param type $key
     *
     * @return \verfriemelt\wrapped\_\Statsd\StatsdClient
     */
    public function decrementCounter($key)
    {
        $this->counter($key, -1);
        return $this;
    }

    public function counter($key, $value)
    {
        $this->send($key, $value, self::COUNTER);
    }

    /**
     * @param type                                   $key
     * @param \verfriemelt\wrapped\_\Statsd\callable $function
     */
    public function time($key, callable $function)
    {
        $timer = new StatsdTimer($this, $key);
        $function();
        $timer->report();
    }

    /**
     * @param type $key
     *
     * @return \verfriemelt\wrapped\_\Statsd\StatsdTimer
     */
    public function createTimer($key)
    {
        return new StatsdTimer($this, $key);
    }

    /**
     * send raw data
     *
     * @param type $key
     * @param type $value
     * @param type $type
     *
     * @return \verfriemelt\wrapped\_\Statsd\StatsdClient
     */
    public function send($key, $value, $type, $rate = null)
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
