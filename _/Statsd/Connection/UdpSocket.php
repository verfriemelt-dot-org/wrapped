<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Statsd\Connection;

use Exception;
use RuntimeException;
use verfriemelt\wrapped\_\DI\Attributes\Env;
use verfriemelt\wrapped\_\Statsd\Connection;
use Override;

final readonly class UdpSocket implements Connection
{
    public function __construct(
        #[Env('STATSD_HOST')]
        private readonly string $host = '127.0.0.1',
        #[Env('STATSD_PORT')]
        private readonly int $port = 8125,
    ) {}

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
        $url = 'udp://' . $this->host;
        $socket = \fsockopen($url, $this->port);

        if ($socket === false) {
            return false;
        }


        try {
            return fwrite($socket, $message) !== false;
        } catch (Exception) {
            return false;
        } finally {
            \fclose($socket);
        }
    }
}
