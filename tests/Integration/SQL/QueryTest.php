<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Integration\SQL;

use verfriemelt\wrapped\_\Database\Driver\Postgres;
use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;
use verfriemelt\wrapped\_\Database\SQL\Statement;
use verfriemelt\wrapped\tests\Integration\DatabaseTestCase;

class QueryTest extends DatabaseTestCase
{
    public function test(): void
    {
        $stmt = new Statement();
        $stmt->setCommand(new Select((new Expression(new Value(1)))->as(new Identifier('test'))));

        $expected = match ((static::$connection)::class) {
            SQLite::class => 1,
            Postgres::class => '1',
        };

        static::assertSame($expected, static::$connection->run($stmt)->fetch()['test']);
    }
}
