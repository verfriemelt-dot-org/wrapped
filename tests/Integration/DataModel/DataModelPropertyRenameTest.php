<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Integration\DataModel;

use verfriemelt\wrapped\_\Database\Driver\Postgres;
use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\Rename;
use verfriemelt\wrapped\_\DataModel\DataModel;
use verfriemelt\wrapped\Tests\Integration\DatabaseTestCase;
use Override;

class RenameTester extends DataModel
{
    public ?int $id = null;

    #[Rename('rAnDoMCAsIng')]
    public ?string $randomCasing = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRandomCasing(): ?string
    {
        return $this->randomCasing;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setRandomCasing(?string $randomCasing): static
    {
        $this->randomCasing = $randomCasing;
        return $this;
    }
}

class DataModelPropertyRenameTest extends DatabaseTestCase
{
    #[Override]
    public function tearDown(): void
    {
        static::$connection->query('drop table if exists "RenameTester" ');
    }

    #[Override]
    public function setUp(): void
    {
        $this->tearDown();

        if (static::$connection instanceof SQLite && static::$connection->getVersion() < 3.35) {
            static::markTestSkipped('returning not supported');
        }

        switch (static::$connection::class) {
            case Postgres::class:
                static::$connection->query('create table "RenameTester" ( id serial not null, "rAnDoMCAsIng" text ) ');
                break;
            case SQLite::class:
                static::$connection->query(
                    'create table "RenameTester" ( id integer primary key not null, "rAnDoMCAsIng" text ) ',
                );
                break;
        }
    }

    public function createInstance(): RenameTester
    {
        (new RenameTester())->save();

        // restore
        return RenameTester::get(1);
    }

    public function test_update(): void
    {
        $instance = $this->createInstance();
        $instance->setRandomCasing('test')->save();

        static::assertNotNull(RenameTester::findSingle(['randomCasing' => 'test']));
        static::assertNotNull(RenameTester::findSingle(['rAnDoMCAsIng' => 'test']));
    }
}
