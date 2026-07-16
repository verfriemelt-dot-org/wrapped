<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Statsd;

use verfriemelt\wrapped\_\Statsd\Connection\UdpSocket;

final readonly class StatsdClient
{
    public const string COUNTER = 'c';
    public const string TIMER_MS = 'ms';
    public const string GAUGE = 'g';

    public function __construct(
        private UdpSocket $connection,
    ) {}

    /**
     * @param array<string,string|int> $labels
     */
    public function incrementCounter(string $key, array $labels = []): self
    {
        $this->counter($key, 1, $labels);
        return $this;
    }

    /**
     * @param array<string,string|int> $labels
     */
    public function gauge(string $key, float $value, array $labels = []): self
    {
        $this->send($key, $labels, $value, self::GAUGE);
        return $this;
    }

    /**
     * @param array<string,string|int> $labels
     */
    public function decrementCounter(string $key, array $labels = []): self
    {
        $this->counter($key, -1, $labels);
        return $this;
    }

    /**
     * @param array<string,string|int> $labels
     */
    public function counter(string $key, int $value, array $labels = []): self
    {
        $this->send($key, $labels, $value, self::COUNTER);
        return $this;
    }

    /**
     * @param array<string,string|int> $labels
     */
    public function time(string $key, callable $function, array $labels = []): void
    {
        $timer = new StatsdTimer($this, $key, $labels);
        $function();
        $timer->report();
    }

    /**
     * @param array<string,string|int> $labels
     */
    public function createTimer(string $key, array $labels = []): StatsdTimer
    {
        return new StatsdTimer($this, $key, $labels);
    }

    /**
     * @param array<string,string|int> $labels
     */
    public function send(string $key, array $labels, int|float $value, string $type): self
    {
        $message = sprintf('%s:%s|%s', $key, $value, $type);

        if ($labels !== []) {
            $list = [];

            foreach ($labels as $labelName => $labelValue) {
                $list[] = "$labelName:$labelValue";
            }

            $message .= '|#' . implode(',', $list);
        }

        $this->connection->send($message);

        return $this;
    }
}
