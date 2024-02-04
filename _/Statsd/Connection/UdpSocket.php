<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Statsd\Connection;

use Exception;
use RuntimeException;
use verfriemelt\wrapped\_\Statsd\Connection;
use Override;

class UdpSocket implements Connection
{
    private $socket;

    private bool $isConnected = false;

    public function __construct(
        private readonly string $host = '127.0.0.1',
        private readonly int $port = 8125,
    ) {}

    public function connect(): static
    {
        $url = 'udp://' . $this->host;
        $this->socket = fsockopen($url, $this->port);
        $this->isConnected = true;
        return $this;
    }

    #[Override]
    public function send(string $message): bool
    {
        if ($message === '') {
            throw new RuntimeException('message cannot be empty');
        }

        return $this->writeToSocket($message);
    }

    public function writeToSocket(string $message): bool
    {
        if (!$this->isConnected) {
            return false;
        }

        try {
            fwrite($this->socket, $message);
        } catch (Exception) {
            return false;
        }

        return true;
    }
}
