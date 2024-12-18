<?php

declare(strict_types=1);

use verfriemelt\wrapped\_\Database\Database;
use verfriemelt\wrapped\_\Database\Driver\Postgres;
use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\DotEnv\DotEnv;
use verfriemelt\wrapped\_\HttpClient\Psr\StreamFactory;
use verfriemelt\wrapped\_\HttpClient\Psr\UriFactory;

require_once __DIR__ . '/../vendor/autoload.php';

const STREAM_FACTORY = StreamFactory::class;
const URI_FACTORY = UriFactory::class;
const TEST_ROOT = __DIR__;

$dotenv = new DotEnv();
$dotenv->load(
    ...array_filter(
        [
            dirname(TEST_ROOT) . '/.env',
            dirname(TEST_ROOT) . '/.env.local',
            dirname(TEST_ROOT) . '/.env.test',
            dirname(TEST_ROOT) . '/.env.test.local',
        ],
        file_exists(...),
    ),
);

match ($_ENV['DATABASE_DRIVER'] ?? null) {
    'sqlite' => Database::createNewConnection('default', SQLite::class, '', '', '', '', 0),
    'postgres' => Database::createNewConnection(
        'default',
        Postgres::class,
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        $_ENV['DB_HOST'],
        $_ENV['DB_NAME'],
        intval($_ENV['DB_PORT']),
    ),
    default => exit('please specify the database driver in your env'),
};
