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
use Override;

class SessionSql extends DataModel implements TablenameOverride, SessionDataObject
{
    public $id;

    public $data;

    public $timeout;

    public $ip;

    public $sessionId;

    #[Override]
    public static function getBySessionId($id)
    {
        return static::findSingle(['sessionId' => $id]);
    }

    #[Override]
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

    #[Override]
    public static function fetchTablename(): string
    {
        return 'Session';
    }

    public function getId()
    {
        return $this->id;
    }

    #[Override]
    public function getData()
    {
        return $this->data;
    }

    #[Override]
    public function getTimeout()
    {
        return $this->timeout;
    }

    #[Override]
    public function getIp()
    {
        return $this->ip;
    }

    #[Override]
    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    #[Override]
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    #[Override]
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    #[Override]
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    #[Override]
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }
}
