<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Database\SQL;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\Facade\QueryBuilder;

class QueryTest extends TestCase
{
    public function test_where_with_value_from_array(): void
    {
        $query = new QueryBuilder();

        $query->select('column');
        $query->from('table');
        $query->where([
            'column' => 1,
        ]);

        static::assertStringContainsString(
            'SELECT column FROM table WHERE column = ',
            $query->fetchStatement()->stringify(),
        );
    }

    public function test_where_with_null_from_array(): void
    {
        $query = new QueryBuilder();

        $query->select('column');
        $query->from('table');
        $query->where([
            'column' => null,
        ]);

        static::assertStringContainsString(
            'SELECT column FROM table WHERE column IS NULL',
            $query->fetchStatement()->stringify(),
        );
    }
}
