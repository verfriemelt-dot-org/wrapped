<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Session;

use verfriemelt\wrapped\_\Database\SQL\Clause\Where;
use verfriemelt\wrapped\_\Database\SQL\Command\Delete;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;
use verfriemelt\wrapped\_\Database\SQL\Statement;
use verfriemelt\wrapped\_\DataModel\DataModel;
use verfriemelt\wrapped\_\DataModel\TablenameOverride;

class SessionSql extends DataModel implements TablenameOverride, SessionDataObject
{
    public $id;

    public $data;

    public $timeout;

    public $ip;

    public $sessionId;

    public static function getBySessionId($id)
    {
        return static::findSingle(['sessionId' => $id]);
    }

    public static function purgeOldSessions()
    {
        $stmt = new Statement(new Delete(new Identifier(static::fetchTablename())));
        $stmt->add(
            new Where(
                (new Expression())
                    ->add(new Identifier('timeout'))
                    ->add(new Operator('<'))
                    ->add(new Value(time()))
            )
        );

        static::fetchDatabase()->run($stmt);
    }

    public static function fetchTablename(): string
    {
        return 'Session';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }
}
