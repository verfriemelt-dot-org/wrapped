<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\Database;
use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\Driver\Postgres;
use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\Http\ParameterBag;

require_once __DIR__ . '/../vendor/autoload.php';

$env = new ParameterBag(getenv());

if (!$env->has('database_driver')) {
    return;
}

match ($env->get('database_driver')) {
    'sqlite' => Database::createNewConnection('default', SQLite::class, '', '', '', '', 0),
    'postgresql' => Database::createNewConnection(
        'default',
        Postgres::class,
        $env->get('db_user', 'docker'),
        $env->get('db_pass', 'docker'),
        $env->get('db_host', 'localhost'),
        $env->get('db_name', 'docker'),
        (int) $env->get('db_port', 5432)
    ),
    default => exit('driver not supported'),
};

abstract class DatabaseTestCase extends TestCase
{
    public static DatabaseDriver $connection;

    public static function setUpBeforeClass(): void
    {
        static::$connection = Database::getConnection();
    }
}
