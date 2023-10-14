<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\DataModel;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;
use verfriemelt\wrapped\_\DataModel\DataModel;

class TablenameExample extends DataModel {}

#[LowerCase]
class TablenameExample2 extends DataModel {}

#[SnakeCase]
class LongerTablenameExample extends DataModel {}

class DataModelTablenameTest extends TestCase
{
    public function test_database_names(): void
    {
        static::assertSame('TablenameExample', TablenameExample::fetchTablename());
        static::assertSame(null, TablenameExample::fetchSchemaname());
    }

    public function test_casing_convention(): void
    {
        static::assertSame('tablenameexample2', TablenameExample2::fetchTablename());
        static::assertSame('longer_tablename_example', LongerTablenameExample::fetchTablename(), 'snake case');
    }
}
