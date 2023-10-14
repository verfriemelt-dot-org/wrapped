<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Integration\DataModel\SimpleTreeDataModel;

use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
use verfriemelt\wrapped\_\DataModel\Tree\SimpleTreeDataModel;
use verfriemelt\wrapped\tests\integration\DatabaseTestCase;

#[LowerCase]
class Tree extends SimpleTreeDataModel
{
    public ?int $id = null;

    public ?int $parentId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
    }
}

class SimpleTreeDataModelTest extends DatabaseTestCase
{
    public function setUp(): void
    {
        if (static::$connection instanceof SQLite) {
            static::markTestSkipped('sqlite not supported');
            return;
        }

        static::$connection->query('create table tree ( id serial primary key, parent_id int );');
    }

    public function tearDown(): void
    {
        static::$connection->query('drop table if exists tree;');
    }

    public function saveInstance($class, $name = 'test')
    {
        $obj = new $class();
        $obj->save();

        return $obj;
    }

    public function test_nested_save()
    {
        $obj1 = new Tree();
        $obj1->save();

        $obj2 = new Tree();
        $obj2->under($obj1);
        $obj2->save();

        static::assertSame($obj1->getId(), $obj2->getParentId());
        static::assertSame($obj1->getId(), $obj2->fetchParent()->getId());
    }

    public function test_children()
    {
        $obj1 = new Tree();
        $obj1->save();

        $obj2 = new Tree();
        $obj2->under($obj1);
        $obj2->save();

        $obj3 = new Tree();
        $obj3->under($obj1);
        $obj3->save();

        $obj4 = new Tree();
        $obj4->under($obj3);
        $obj4->save();

        static::assertSame(2, $obj1->fetchDirectChildren()->count());
        static::assertSame(3, $obj1->fetchChildren()->count());

        static::assertSame(3, $obj1->fetchChildCount());
        static::assertSame(0, $obj2->fetchChildCount());
        static::assertSame(1, $obj3->fetchChildCount());
    }

    public function test_fetch_parent()
    {
        $obj1 = new Tree();
        $obj1->save();

        $obj2 = new Tree();
        $obj2->under($obj1);
        $obj2->save();

        $obj3 = new Tree();
        $obj3->under($obj1);
        $obj3->save();

        $obj4 = new Tree();
        $obj4->under($obj3);
        $obj4->save();

        static::assertSame($obj3->getId(), $obj4->fetchParent()->getId());
        static::assertSame($obj1->getId(), $obj4->fetchParent()->fetchParent()->getId());
        static::assertNull($obj4->fetchParent()->fetchParent()->fetchParent());
    }
}
