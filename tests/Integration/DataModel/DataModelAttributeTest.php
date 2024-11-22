<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Integration\DataModel;

use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\CamelCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;
use verfriemelt\wrapped\_\DataModel\DataModel;
use verfriemelt\wrapped\Tests\Integration\DatabaseTestCase;
use Override;

#[LowerCase]
class LowerDummy extends DataModel
{
    protected int $id;

    #[LowerCase]
    protected ?string $complexFieldName = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getComplexFieldName(): ?string
    {
        return $this->complexFieldName;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setComplexFieldName(?string $complexFieldName): static
    {
        $this->complexFieldName = $complexFieldName;
        return $this;
    }
}

#[CamelCase]
class CamelCaseDummy extends LowerDummy
{
    #[CamelCase]
    public ?string $complexFieldName = null;
}

#[SnakeCase]
class SnakeCaseDummy extends LowerDummy
{
    #[SnakeCase]
    public ?string $complexFieldName = null;
}

class DataModelAttributeTest extends DatabaseTestCase
{
    #[Override]
    public function setUp(): void
    {
        if (static::$connection instanceof SQLite && static::$connection->getVersion() < 3.35) {
            static::markTestSkipped('returning not supported');
        }

        parent::setUp();

        if (static::$connection instanceof SQLite) {
            static::$connection->query('create table lowerdummy ( id integer primary key not null, complexfieldname text );');
            static::$connection->query('create table "camelCaseDummy" ( id integer primary key not null, "complexFieldName" text );');
            static::$connection->query('create table snake_case_dummy ( id integer primary key not null, complex_field_name text );');
        } else {
            static::$connection->query('create table lowerdummy ( id serial primary key not null, complexfieldname text );');
            static::$connection->query('create table "camelCaseDummy" ( id serial primary key not null, "complexFieldName" text );');
            static::$connection->query('create table snake_case_dummy ( id serial primary key not null, complex_field_name text );');
        }


    }

    #[Override]
    public function tearDown(): void
    {
        static::$connection->query('drop table if exists dummy;');
        static::$connection->query('drop table if exists lowerdummy ;');
        static::$connection->query('drop table if exists snake_case_dummy;');
        static::$connection->query('drop table if exists "camelCaseDummy";');
    }

    public function saveInstance(string $class, string $name = 'test')
    {
        $obj = new $class();
        $obj->setComplexFieldName($name);
        $obj->save();

        return $obj;
    }

    public function testall_lower(): void
    {
        $this->saveInstance(LowerDummy::class, 'test');

        static::assertNotNull(LowerDummy::findSingle(['complexfieldname' => 'test']));
        static::assertNotNull(LowerDummy::findSingle(['complexFieldName' => 'test']));
    }

    public function test_camel_case(): void
    {
        $this->saveInstance(CamelCaseDummy::class, 'test');

        static::assertNotNull(CamelCaseDummy::findSingle(['complexFieldName' => 'test']));
    }

    public function test_snake_case(): void
    {
        $this->saveInstance(SnakeCaseDummy::class, 'test');

        static::assertNotNull(SnakeCaseDummy::findSingle(['complex_field_name' => 'test']));
        static::assertNotNull(SnakeCaseDummy::findSingle(['complexFieldName' => 'test']));

        static::assertSame('test', SnakeCaseDummy::findSingle(['complex_field_name' => 'test'])->getComplexFieldName());
    }
}
