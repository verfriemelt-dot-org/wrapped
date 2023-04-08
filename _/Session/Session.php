<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Session;

use verfriemelt\wrapped\_\Http\Request\Request;

final class Session implements SessionHandler
{
    public const SESSION_COOKIE_NAME = '_';

    public const SESSION_TIMEOUT = 60 * 60 * 24 * 365;

    private $dataObj = null;

    private ?string $sessionId = null;

    private string|\verfriemelt\wrapped\_\Session\SessionDataObject|null $storageObj = null;

    private $currentData = [];

    private readonly Request $request;

    public function __construct(Request $request, SessionDataObject $sessionStorage = null)
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
            return false;
        }

        $this->dataObj->setTimeout(time() + static::SESSION_TIMEOUT);
        $this->dataObj->setData(base64_encode(serialize($this->currentData)));
        $this->dataObj->save();
    }

    public function delete($name)
    {
        unset($this->currentData[$name]);
        return $this;
    }

    public function destroy()
    {
        $this->dataObj->delete();
        $this->currentData = [];

        setcookie(
            self::SESSION_COOKIE_NAME,
            '',
            ['expires' => time() - self::SESSION_TIMEOUT * 10]
        );
    }

    public function get($name, $default = null)
    {
        if ($this->has($name)) {
            return $this->currentData[$name];
        }

        return $default;
    }

    public function has($name)
    {
        return isset($this->currentData[$name]);
    }

    /**
     * we have to copy it locally, since its not possible to to stuff like
     * $htis->storageObj::staticCall();
     *
     * @param type $sessionId
     *
     * @return Session
     */
    private function resume($sessionId)
    {
        $localCopy = $this->storageObj;

        $this->dataObj = $localCopy::getBySessionId($sessionId);

        // if not found, create new session
        if ($this->dataObj === null) {
            return $this->start();
        }

        $this->sessionId = $sessionId;
        $this->currentData = unserialize(base64_decode((string) $this->dataObj->getData()));

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return Session
     */
    public function set($name, $value)
    {
        if ($this->sessionId === null) {
            $this->start();
        }

        $this->currentData[$name] = $value;
        return $this;
    }

    private function start()
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

    public function fetchSessionId()
    {
        if ($this->sessionId === null) {
            $this->start();
        }

        return $this->sessionId;
    }
}
