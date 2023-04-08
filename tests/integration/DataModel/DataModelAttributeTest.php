<?php

declare(strict_types=1);

namespace integration\DataModel\AttributeTest;

use DatabaseTestCase;
use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\CamelCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;
use verfriemelt\wrapped\_\DataModel\DataModel;

#[LowerCase]
class LowerDummy extends DataModel
{
    public ?int $id = null;

    #[LowerCase]
    public ?string $complexFieldName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComplexFieldName(): ?string
    {
        return $this->complexFieldName;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setComplexFieldName(?string $complexFieldName)
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
    public function setUp(): void
    {
        if (static::$connection instanceof SQLite && static::$connection->getVersion() < 3.35) {
            static::markTestSkipped('returning not supported');
        }

        parent::setUp();
    }

    public function tearDown(): void
    {
        static::$connection->query('drop table if exists dummy;');
        static::$connection->query('drop table if exists lowerdummy ;');
        static::$connection->query('drop table if exists snake_case_dummy;');
        static::$connection->query('drop table if exists "camelCaseDummy";');
    }

    public function saveInstance($class, $name = 'test')
    {
        $obj = new $class();
        $obj->setComplexFieldName($name);
        $obj->save();

        return $obj;
    }

    public function testallLower()
    {
        static::$connection->query('create table lowerdummy ( id serial primary key, complexfieldname text );');
        $this->saveInstance(LowerDummy::class, 'test');

        static::assertNotNull(LowerDummy::findSingle(['complexfieldname' => 'test']));
        static::assertNotNull(LowerDummy::findSingle(['complexFieldName' => 'test']));
    }

    public function testCamelCase()
    {
        static::$connection->query('create table "camelCaseDummy" ( id serial primary key, "complexFieldName" text );');
        $this->saveInstance(CamelCaseDummy::class, 'test');

        static::assertNotNull(CamelCaseDummy::findSingle(['complexFieldName' => 'test']));
    }

    public function testSnakeCase()
    {
        static::$connection->query('create table snake_case_dummy ( id serial primary key, complex_field_name text );');
        $this->saveInstance(SnakeCaseDummy::class, 'test');

        static::assertNotNull(SnakeCaseDummy::findSingle(['complex_field_name' => 'test']));
        static::assertNotNull(SnakeCaseDummy::findSingle(['complexFieldName' => 'test']));

        static::assertSame('test', SnakeCaseDummy::findSingle(['complex_field_name' => 'test'])->getComplexFieldName());
    }
}
