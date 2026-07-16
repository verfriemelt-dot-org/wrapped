<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Statsd;

final class StatsdTimer
{
    private float $start = 0;
    private ?float $diff = null;

    /**
     * @param array<string,string|int> $labels
     */
    public function __construct(
        private readonly StatsdClient $statsd,
        private readonly string $name,
        private readonly array $labels = [],
    ) {
        $this->restart();
    }

    /**
     * reports back to statsd instance
     */
    public function report(): void
    {
        if ($this->diff === null) {
            $this->end();
        }

        assert($this->diff !== null);

        $this->statsd->send($this->name, $this->labels, $this->diff, StatsdClient::TIMER_MS);
    }

    /**
     * sets set startingtime to the current time
     */
    public function restart(): void
    {
        $this->start = microtime(true);
    }

    public function end(): void
    {
        $this->diff = round(microtime(true) * 1000) - round($this->start * 1000);
    }

    public function getTime(): ?float
    {
        return $this->diff;
    }
}
