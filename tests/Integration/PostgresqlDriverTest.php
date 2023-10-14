<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Integration;

use RuntimeException;

class PostgresqlDriverTest extends DatabaseTestCase
{
    public function test_exception_on_rollingback_nonexisting_transactions(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('no active transaction');
        static::$connection->rollbackTransaction();
    }

    public function test_exception_on_committing_nonexisting_transactions(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('no active transaction');
        static::$connection->commitTransaction();
    }

    public function test_start_transactions_and_rolling_back(): void
    {
        static::expectNotToPerformAssertions();

        static::$connection->startTransaction();
        static::$connection->rollbackTransaction();
    }

    public function test_nesting_transactions(): void
    {
        static::$connection->startTransaction();
        static::$connection->query('CREATE TABLE  foo (id int not null)');

        static::$connection->startTransaction();
        static::$connection->query('insert into foo values (1)');
        static::$connection->startTransaction();
        static::$connection->query('insert into foo values (2)');
        static::$connection->rollbackTransaction();

        $result = static::$connection->query('select count(*) from foo');
        static::assertSame(1, $result->fetchColumn(), 'expect only on value in table');

        static::$connection->rollbackTransaction();
    }
}
