<?php

    namespace integration\DataModel\FindTest;

    use \DatabaseTestCase;
    use \verfriemelt\wrapped\_\Database\Driver\Postgres;
    use \verfriemelt\wrapped\_\Database\Driver\SQLite;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\Rename;
    use \verfriemelt\wrapped\_\DataModel\DataModel;

    class RenameTester
    extends DataModel {

        public ?int $id = null;

        #[ Rename( 'rAnDoMCAsIng' ) ]
        public ?string $randomCasing = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getRandomCasing(): ?string {
            return $this->randomCasing;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setRandomCasing( ?string $randomCasing ) {
            $this->randomCasing = $randomCasing;
            return $this;
        }

    }

    class DataModelPropertyRenameTest
    extends DatabaseTestCase {

        public function tearDown(): void {
            static::$connection->query( "drop table if exists \"RenameTester\" " );
        }

        public function setUp(): void {
            $this->tearDown();

            switch ( static::$connection::class ) {
                case Postgres::class:
                    static::$connection->query( "create table \"RenameTester\" ( id serial, \"rAnDoMCAsIng\" text ) " );
                    break;
                case SQLite::class:
                    static::$connection->query( "create table \"RenameTester\" ( id integer primary key, \"rAnDoMCAsIng\" text ) " );
                    break;
            }
        }

        public function createInstance(): RenameTester {

            (new RenameTester() )->save();

            // restore
            return RenameTester::get( 1 );
        }

        public function testUpdate() {

            $instance = $this->createInstance();
            $instance->setRandomCasing( 'test' )->save();

            static::assertNotNull( RenameTester::findSingle( [ 'randomCasing' => 'test' ] ) );
            static::assertNotNull( RenameTester::findSingle( [ 'rAnDoMCAsIng' => 'test' ] ) );
        }

    }
