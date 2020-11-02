<?php

    namespace functional;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\TreeDataModel;

    class TreeDummy
    extends TreeDataModel {

        public ?int $id = null;

        public ?string $name = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function getName(): ?string {
            return $this->name;
        }

        public function setName( ?string $name ) {
            $this->name = $name;
            return $this;
        }

    }

    class FunctionalTreeDataModelTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
            $this->tearDown();
            static::$connection->query( 'create table "TreeDummy" ( id serial primary key, name text, "left" int, "right" int, parent_id int, depth int );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table if exists "TreeDummy";' );
        }

        public function test() {
            new TreeDummy;
        }

        public function testSave() {
            $dummy = new TreeDummy;
            $dummy->save();
        }

    }
