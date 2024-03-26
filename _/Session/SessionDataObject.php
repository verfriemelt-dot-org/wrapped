<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Session;

interface SessionDataObject
{
    public static function getBySessionId(string $id): ?SessionDataObject;

    public static function purgeOldSessions(): void;

    public function getData(): string;

    public function getTimeout(): int;

    public function getIp(): string;

    public function getSessionId(): string;

    public function setData(string $data): SessionDataObject;

    public function setTimeout(int $timeout): SessionDataObject;

    public function setIp(string $ip);

    public function setSessionId(string $sessionId): SessionDataObject;

    public function save(): static;

    public function delete(): static;
}
