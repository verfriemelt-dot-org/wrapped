<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Exception\Database\DatabaseDriverUnknown;
use verfriemelt\wrapped\_\Exception\Database\DatabaseException;

final class Database
{
    private static array $connections = [];

    public static function createNewConnection(
        $name,
        $driver,
        $username,
        $password,
        $host,
        $database,
        $port,
        $autoConnect = true,
    ): DatabaseDriver {
        if (!class_exists($driver)) {
            throw new DatabaseDriverUnknown("unknown driver {$driver}");
        }

        self::$connections[$name] = new $driver($name, $username, $password, $host, $database, $port);

        if ($autoConnect) {
            self::$connections[$name]->connect();
        }

        return self::$connections[$name];
    }

    /**
     * @throws DatabaseException
     */
    public static function getConnection(string $name = 'default'): DatabaseDriver
    {
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        throw new DatabaseException('No connection by that name, sorry');
    }

    public static function clear(): void
    {
        self::$connections = [];
    }
}
