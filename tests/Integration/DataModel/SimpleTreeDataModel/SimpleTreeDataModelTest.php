<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Integration\DataModel\SimpleTreeDataModel;

use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
use verfriemelt\wrapped\_\DataModel\Tree\SimpleTreeDataModel;
use Override;
use verfriemelt\wrapped\Tests\Integration\DatabaseTestCase;

#[LowerCase]
class Tree extends SimpleTreeDataModel
{
    public int $id;
    public ?int $parentId = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setId(int $id): void
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
    #[Override]
    public function setUp(): void
    {
        if (static::$connection instanceof SQLite) {
            static::markTestSkipped('sqlite not supported');
        }

        static::$connection->query('create table tree ( id serial primary key, parent_id int );');
    }

    #[Override]
    public function tearDown(): void
    {
        static::$connection->query('drop table if exists tree;');
    }

    public function saveInstance(string $class): SimpleTreeDataModel
    {
        $obj = new $class();
        $obj->save();

        return $obj;
    }

    public function test_nested_save(): void
    {
        $obj1 = new Tree();
        $obj1->save();

        $obj2 = new Tree();
        $obj2->under($obj1);
        $obj2->save();

        static::assertSame($obj1->getId(), $obj2->getParentId());
        static::assertSame($obj1->getId(), $obj2->fetchParent()->getId());
    }

    public function test_children(): void
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

    public function test_fetch_parent(): void
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
