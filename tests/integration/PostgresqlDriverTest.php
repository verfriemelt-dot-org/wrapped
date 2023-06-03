<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\integration;

use RuntimeException;

class PostgresqlDriverTest extends DatabaseTestCase
{
    public function testExceptionOnRollingbackNonexistingTransactions(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('no active transaction');
        static::$connection->rollbackTransaction();
    }

    public function testExceptionOnCommittingNonexistingTransactions(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('no active transaction');
        static::$connection->commitTransaction();
    }

    public function testStartTransactionsAndRollingBack(): void
    {
        static::expectNotToPerformAssertions();

        static::$connection->startTransaction();
        static::$connection->rollbackTransaction();
    }

    public function testNestingTransactions(): void
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
