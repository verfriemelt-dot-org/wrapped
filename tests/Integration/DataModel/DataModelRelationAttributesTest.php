<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Integration\DataModel;

use Exception;
use verfriemelt\wrapped\_\Database\Driver\Postgres;
use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\DataModel\Attribute\Relation\OneToOneRelation;
use verfriemelt\wrapped\_\DataModel\DataModel;
use Override;
use verfriemelt\wrapped\Tests\Integration\DatabaseTestCase;

class A extends DataModel
{
    protected int $id;
    protected ?int $bId = null;
    protected ?B $aObject = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBId(): ?int
    {
        return $this->bId;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setBId(?int $bId): static
    {
        $this->bId = $bId;
        return $this;
    }
}

/**
 * @method aObject()
 * @method aObjectNotMarked()
 * @method aWrongMarked()
 */
class B extends DataModel
{
    protected int $id;
    protected ?int $aId = null;

    #[OneToOneRelation('aId', 'id')]
    protected ?A $aObject = null;

    #[OneToOneRelation('aId', 'did')]
    protected ?A $aWrongMarked = null;

    protected ?A $aObjectNotMarked = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAId(): ?int
    {
        return $this->aId;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setAId(?int $aId): static
    {
        $this->aId = $aId;
        return $this;
    }
}

class DataModelRelationAttributesTest extends DatabaseTestCase
{
    #[Override]
    public function setUp(): void
    {
        if (static::$connection instanceof SQLite && static::$connection->getVersion() < 3.35) {
            static::markTestSkipped('returning not supported');
        }

        switch (static::$connection::class) {
            case Postgres::class:
                static::$connection->query('create table "A" ( id serial primary key, b_id int );');
                static::$connection->query('create table "B" ( id serial primary key, a_id int );');
                break;
            case SQLite::class:
                static::$connection->query('create table "A" ( id integer primary key not null, b_id int );');
                static::$connection->query('create table "B" ( id integer primary key not null, a_id int );');
                break;
        }
    }

    #[Override]
    public function tearDown(): void
    {
        static::$connection->query('drop table if exists "A";');
        static::$connection->query('drop table if exists "B";');
    }

    public function buildObjects()
    {
        (new B())->save();
        (new A())->setBId(1)->save();

        B::get(1)->setAId(1)->save();
    }

    public function test_not_prepped(): void
    {
        $this->buildObjects();
        $this->expectExceptionObject(new Exception('attribute'));

        B::get(1)->aObjectNotMarked();
    }

    public function test_resolv(): void
    {
        $this->buildObjects();

        static::assertSame(1, B::get(1)->getAId());
        static::assertSame(1, B::get(1)->aObject()->getId());
    }
}
