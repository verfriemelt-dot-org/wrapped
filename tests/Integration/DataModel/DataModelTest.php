<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Integration\DataModel;

use verfriemelt\wrapped\_\Database\Database;
use verfriemelt\wrapped\_\Database\Driver\Postgres;
use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\DataModel\DataModel;
use verfriemelt\wrapped\tests\integration\DatabaseTestCase;
use Override;

class Dummy extends DataModel
{
    #[test]
    public ?int $id = null;

    public ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }
}

class DataModelTest extends DatabaseTestCase
{
    #[Override]
    public function setUp(): void
    {
        if (static::$connection instanceof SQLite && static::$connection->getVersion() < 3.35) {
            static::markTestSkipped('returning not supported');
        }

        switch (static::$connection::class) {
            case Postgres::class:
                static::$connection->query(
                    'create table "Dummy" ( id int GENERATED BY DEFAULT AS IDENTITY primary key , name text );'
                );
                break;
            case SQLite::class:
                if (static::$connection->getVersion() < 3.35) {
                    static::markTestSkipped('nope');
                }

                static::$connection->query('create table "Dummy" ( id integer primary key , name text );');
                break;
        }
    }

    #[Override]
    public function tearDown(): void
    {
        static::$connection->query('drop table "Dummy" ;');
    }

    public function saveInstance($name = 'test')
    {
        $obj = new Dummy();
        $obj->setName($name);
        $obj->save();

        return $obj;
    }

    public function test_object_save_auto_generate_id()
    {
        $obj = $this->saveInstance();

        static::assertSame(1, $obj->getId());
    }

    public function test_object_get()
    {
        $this->saveInstance();
        $newObj = Dummy::get(1);

        static::assertSame('test', $newObj->getName());
    }

    public function test_object_fetch()
    {
        $this->saveInstance('test1');
        $this->saveInstance('test2');
        $this->saveInstance('test3');

        $newObj = Dummy::findSingle(['id' => 1, 'name' => 'test1']);

        static::assertSame('test1', $newObj->getName());
    }

    public function test_object_fetch_with_array()
    {
        $this->saveInstance('test1');
        $this->saveInstance('test2');
        $this->saveInstance('test3');

        $newObj = Dummy::findSingle(['id' => [1, 2, 3], 'name' => 'test2']);

        static::assertSame('test2', $newObj->getName());
    }

    public function test_object_fetch_sorted()
    {
        $this->saveInstance('test');
        $this->saveInstance('test');
        $this->saveInstance('test');

        static::assertSame(3, Dummy::findSingle(['name' => 'test'], 'id', 'desc')->getId());
        static::assertSame(1, Dummy::findSingle(['name' => 'test'], 'id')->getId());
    }

    public function test_object_update()
    {
        $this->saveInstance('test');
        $this->saveInstance('test');
        $this->saveInstance('test');

        $newObj = Dummy::get(1)->setName('updated')->save();

        static::assertSame('test', Dummy::get(2)->getName(), 'should not update other objects');
        static::assertSame('updated', Dummy::get(1)->getName(), 'should have updated itself');
    }

    public function test_object_delete()
    {
        $this->saveInstance('test');
        $this->saveInstance('test');
        $this->saveInstance('test');

        Dummy::get(1)->delete();

        static::assertSame(2, Dummy::count('id'), 'only two should remain');
    }

    public function test_object_reload()
    {
        $obj = new Dummy();
        $obj->setName('test');
        $obj->save();

        Database::getConnection()->query("update \"Dummy\" set name = 'epic'");

        $obj->reload();

        static::assertSame('epic', $obj->getName());
    }

    public function test_object_all()
    {
        $this->saveInstance('test');
        $this->saveInstance('test');
        $this->saveInstance('test');

        static::assertSame(3, count(Dummy::all()));
    }
}
