<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Session;

use Override;

final class NullSession implements SessionDataObject
{
    #[Override]
    public static function getBySessionId(string $id): ?SessionDataObject
    {
        return null;
    }

    #[Override]
    public static function purgeOldSessions(): void {}

    #[Override]
    public function getData(): string
    {
        return '';
    }

    #[Override]
    public function getTimeout(): int
    {
        return 0;
    }

    #[Override]
    public function getIp(): string
    {
        return '127.0.0.1';
    }

    #[Override]
    public function getSessionId(): string
    {
        return 'sessionId';
    }

    #[Override]
    public function setData(string $data): SessionDataObject
    {
        return $this;
    }

    #[Override]
    public function setTimeout(int $timeout): SessionDataObject
    {
        return $this;
    }

    #[Override]
    public function setIp(string $ip): SessionDataObject
    {
        return $this;
    }

    #[Override]
    public function setSessionId(string $sessionId): SessionDataObject
    {
        return $this;
    }

    #[Override]
    public function save(): static
    {
        return $this;
    }

    #[Override]
    public function delete(): static
    {
        return $this;
    }
}
