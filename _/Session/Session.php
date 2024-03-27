<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Session;

use Override;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\ParameterBag;

final class Session implements SessionHandler
{
    public const string SESSION_COOKIE_NAME = '_';

    public const SESSION_TIMEOUT = 60 * 60 * 24 * 365;

    private ?string $sessionId = null;

    private ParameterBag $data;

    private bool $inUse = false;

    public function __construct(
        private readonly Request $request,
        private SessionDataObject $storage
    ) {
        $this->data = new ParameterBag();
        ($this->storage)::purgeOldSessions();

        if ($request->cookies()->has(self::SESSION_COOKIE_NAME)) {
            $this->resume($request->cookies()->get(self::SESSION_COOKIE_NAME));
        }
    }

    public function shutdown(): void
    {
        if (!$this->inUse) {
            return;
        }

        $this->storage->setTimeout(time() + static::SESSION_TIMEOUT);
        $this->storage->setData(\json_encode($this->data->all(), \JSON_THROW_ON_ERROR));
        $this->storage->save();
    }

    #[Override]
    public function delete(string $name): static
    {
        $this->inUse = true;
        $this->data->delete($name);
        return $this;
    }

    #[Override]
    public function destroy(): void
    {
        $this->storage->delete();
        $this->data = new ParameterBag();

        setcookie(
            self::SESSION_COOKIE_NAME,
            '',
            ['expires' => time() - self::SESSION_TIMEOUT * 10]
        );
    }

    #[Override]
    public function get(string $name, mixed $default = null): mixed
    {
        if ($this->has($name)) {
            return $this->data->get($name);
        }

        return $default;
    }

    #[Override]
    public function has(string $name): bool
    {
        return $this->data->has($name);
    }

    private function resume(string $sessionId): static
    {
        $storage = ($this->storage)::getBySessionId($sessionId);

        // if not found, create new session
        if ($storage === null) {
            $this->start();
            return $this;
        }

        $this->storage = $storage;
        $this->sessionId = $sessionId;

        $sessionData = \json_decode($this->storage->getData(), true);
        if (!is_array($sessionData)) {
            $this->data = new ParameterBag();
            $this->inUse = true;
        } else {
            $this->data = new ParameterBag($sessionData);
        }

        return $this;
    }

    #[Override]
    public function set(string $name, mixed $value): static
    {
        $this->inUse = true;

        if ($this->sessionId === null) {
            $this->start();
        }

        $this->data->set($name, (string) $value);
        return $this;
    }

    private function start(): void
    {
        $this->sessionId = sha1(microtime(true) . random_int(0, mt_getrandmax()));
        $this->inUse = true;

        setcookie(
            self::SESSION_COOKIE_NAME,
            $this->sessionId,
            ['expires' => time() + self::SESSION_TIMEOUT, 'path' => '/']
        );

        $this->storage = new $this->storage();

        $this->storage->setSessionId($this->sessionId);
        $this->storage->setIp($this->request->remoteIp());
        $this->storage->setTimeout(time() + self::SESSION_TIMEOUT);
    }

    public function fetchSessionId(): string
    {
        if ($this->sessionId === null) {
            $this->start();
        }

        return $this->sessionId;
    }

    public function all(): array
    {
        return $this->data->all();
    }
}
