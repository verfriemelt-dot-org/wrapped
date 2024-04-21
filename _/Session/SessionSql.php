<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Session;

use Override;
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
    protected int $id;
    protected string $data;
    protected int $timeout;
    protected string $ip;
    protected string $sessionId;

    #[Override]
    public static function getBySessionId(string $id): ?static
    {
        return static::findSingle(['sessionId' => $id]);
    }

    #[Override]
    public static function purgeOldSessions(): void
    {
        $stmt = new Statement(new Delete(new Identifier(static::fetchTablename())));
        $stmt->add(
            new Where(
                (new Expression())
                    ->add(new Identifier('timeout'))
                    ->add(new Operator('<'))
                    ->add(new Value(time())),
            ),
        );

        static::fetchDatabase()->run($stmt);
    }

    #[Override]
    public static function fetchTablename(): string
    {
        return 'Session';
    }

    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getData(): string
    {
        return $this->data;
    }

    #[Override]
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    #[Override]
    public function getIp(): string
    {
        return $this->ip;
    }

    #[Override]
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    #[Override]
    public function setData(string $data): static
    {
        $this->data = $data;
        return $this;
    }

    #[Override]
    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    #[Override]
    public function setIp(string $ip): static
    {
        $this->ip = $ip;
        return $this;
    }

    #[Override]
    public function setSessionId(string $sessionId): static
    {
        $this->sessionId = $sessionId;
        return $this;
    }
}
