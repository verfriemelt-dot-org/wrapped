<?php

    namespace functional;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\ObjectAnalyser;

    class Dummy
    extends DataModel {

        public ?int $id = null;

        public ?string $name = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        function getName(): ?string {
            return $this->name;
        }

        function setName( ?string $name ): void {
            $this->name = $name;
        }

    }

    class FunctionDataModelTest
    extends TestCase {

        static $connection;

        public function setUp(): void {

            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
            static::$connection->query( 'create table if not exists dummy ( id serial primary key, name text );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table if exists dummy ;' );
        }

        public function testObject() {

            $obj = new Dummy;
            $obj->setName( 'test' );
            $obj->save();

            $newObj = Dummy::get( 1 );

            $this->assertSame( 'test', $newObj->getName() );
        }

        public function testObjectReload() {

            $obj = new Dummy;
            $obj->setName( 'test' );
            $obj->save();

            Database::getConnection()->query("update dummy set name = 'epic'");

            $obj->reload();

            $this->assertSame( 'test', $obj->getName() );
        }

    }
