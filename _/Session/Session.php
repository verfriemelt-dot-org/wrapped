<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Session;

use verfriemelt\wrapped\_\Http\Request\Request;
use Override;

final class Session implements SessionHandler
{
    public const string SESSION_COOKIE_NAME = '_';

    public const SESSION_TIMEOUT = 60 * 60 * 24 * 365;

    private $dataObj;

    private ?string $sessionId = null;

    private string|SessionDataObject|null $storageObj = null;

    /** @var mixed[] */
    private array $currentData = [];

    private readonly Request $request;

    public function __construct(Request $request, ?SessionDataObject $sessionStorage = null)
    {
        $this->request = $request;

        if ($sessionStorage === null || !in_array(SessionDataObject::class, class_implements($sessionStorage))) {
            $this->storageObj = SessionSql::class;
        } else {
            $this->storageObj = $sessionStorage;
        }

        $this->storageObj::purgeOldSessions();

        if ($request->cookies()->has(self::SESSION_COOKIE_NAME)) {
            $this->resume($request->cookies()->get(self::SESSION_COOKIE_NAME));
        }
    }

    public function __destruct()
    {
        if ($this->dataObj === null) {
            return;
        }

        $this->dataObj->setTimeout(time() + static::SESSION_TIMEOUT);
        $this->dataObj->setData(base64_encode(serialize($this->currentData)));
        $this->dataObj->save();
    }

    #[Override]
    public function delete(string $name): static
    {
        unset($this->currentData[$name]);
        return $this;
    }

    #[Override]
    public function destroy(): void
    {
        $this->dataObj->delete();
        $this->currentData = [];

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
            return $this->currentData[$name];
        }

        return $default;
    }

    #[Override]
    public function has(string $name): bool
    {
        return isset($this->currentData[$name]);
    }

    private function resume(string $sessionId): static
    {
        $localCopy = $this->storageObj;

        $this->dataObj = $localCopy::getBySessionId($sessionId);

        // if not found, create new session
        if ($this->dataObj === null) {
            $this->start();
            return $this;
        }

        $this->sessionId = $sessionId;
        $this->currentData = unserialize(base64_decode((string) $this->dataObj->getData()));

        return $this;
    }

    #[Override]
    public function set(string $name, mixed $value): static
    {
        if ($this->sessionId === null) {
            $this->start();
        }

        $this->currentData[$name] = $value;
        return $this;
    }

    private function start(): void
    {
        $this->sessionId = sha1(microtime(true) . random_int(0, mt_getrandmax()));

        setcookie(
            self::SESSION_COOKIE_NAME,
            $this->sessionId,
            ['expires' => time() + self::SESSION_TIMEOUT, 'path' => '/']
        );

        $this->dataObj = new $this->storageObj();

        $this->dataObj->setSessionId($this->sessionId);
        $this->dataObj->setIp($this->request->remoteIp());
        $this->dataObj->setTimeout(time() + self::SESSION_TIMEOUT);
    }

    public function fetchSessionId(): string
    {
        if ($this->sessionId === null) {
            $this->start();
        }

        return $this->sessionId;
    }
}
