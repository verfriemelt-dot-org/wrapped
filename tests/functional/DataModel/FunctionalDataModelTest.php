<?php

    namespace functional;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\TablenameOverride;

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

        public function getName(): ?string {
            return $this->name;
        }

        public function setName( ?string $name ) {
            $this->name = $name;
            return $this;
        }

    }

    class FunctionDataModelTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
            static::$connection->query( 'create table "Dummy" ( id serial primary key, name text );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table "Dummy" ;' );
        }

        public function saveInstance( $name = 'test' ) {

            $obj = new Dummy;
            $obj->setName( $name );
            $obj->save();

            return $obj;
        }

        public function testObjectSaveAutoGenerateId() {

            $obj = $this->saveInstance();

            $this->assertSame( 1, $obj->getId() );
        }

        public function testObjectGet() {

            $this->saveInstance();
            $newObj = Dummy::get( 1 );

            $this->assertSame( 'test', $newObj->getName() );
        }

        public function testObjectFetch() {

            $this->saveInstance( 'test1' );
            $this->saveInstance( 'test2' );
            $this->saveInstance( 'test3' );

            $newObj = Dummy::findSingle( [ 'id' => 1, 'name' => 'test1' ] );

            $this->assertSame( 'test1', $newObj->getName() );
        }

        public function testObjectFetchWithArray() {

            $this->saveInstance( 'test1' );
            $this->saveInstance( 'test2' );
            $this->saveInstance( 'test3' );

            $newObj = Dummy::findSingle( [ 'id' => [ 1, 2, 3 ], 'name' => 'test2' ] );

            $this->assertSame( 'test2', $newObj->getName() );
        }

        public function testObjectFetchSorted() {

            $this->saveInstance( 'test' );
            $this->saveInstance( 'test' );
            $this->saveInstance( 'test' );

            $this->assertSame( 3, Dummy::findSingle( [ 'name' => 'test' ], 'id', 'desc' )->getId() );
            $this->assertSame( 1, Dummy::findSingle( [ 'name' => 'test' ], 'id' )->getId() );
        }

        public function testObjectUpdate() {

            $this->saveInstance( 'test' );
            $this->saveInstance( 'test' );
            $this->saveInstance( 'test' );

            $newObj = Dummy::get( 1 )->setName( 'updated' )->save();

            $this->assertSame( 'test', Dummy::get( 2 )->getName(), 'should not update other objects' );
            $this->assertSame( 'updated', Dummy::get( 1 )->getName(), 'should have updated itself' );
        }

        public function testObjectDelete() {

            $this->saveInstance( 'test' );
            $this->saveInstance( 'test' );
            $this->saveInstance( 'test' );

            $newObj = Dummy::get( 1 )->delete();

            $this->assertSame( 2, Dummy::count(), 'only two should remain' );
        }

        public function testObjectReload() {

            $obj = new Dummy;
            $obj->setName( 'test' );
            $obj->save();

            Database::getConnection()->query( "update \"Dummy\" set name = 'epic'" );

            $obj->reload();

            $this->assertSame( 'epic', $obj->getName() );
        }

        public function testObjectAll() {

            $this->saveInstance( 'test' );
            $this->saveInstance( 'test' );
            $this->saveInstance( 'test' );

            $this->assertSame( 3, count( Dummy::all() ) );
        }

    }
