<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\Driver;

use RuntimeException;
use Override;

class SQLite extends DatabaseDriver
{
    final public const PDO_NAME = 'sqlite::memory:';

    private string $databaseVersion;

    #[Override]
    protected function getConnectionString(): string
    {
        return self::PDO_NAME;
    }

    #[Override]
    public function quoteIdentifier(string $ident): string
    {
        return sprintf('"%s"', $ident);
    }

    #[Override]
    public function connect(): void
    {
        parent::connect();

        $result = $this->query('SELECT sqlite_version()')->fetchColumn();

        if (!is_string($result)) {
            throw new RuntimeException('cannot fetch version');
        }

        $this->databaseVersion = $result;
    }

    public function getVersion(): float
    {
        $parts = explode('.', $this->databaseVersion);

        return (float) "{$parts[0]}.{$parts[1]}";
    }
}
