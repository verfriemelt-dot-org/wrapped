<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Integration;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\Database;
use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;

abstract class DatabaseTestCase extends TestCase
{
    public static DatabaseDriver $connection;

    public static function setUpBeforeClass(): void
    {
        static::$connection = Database::getConnection();
    }
}
