<?php

    namespace functional\DataModel\FindTest;

    use \functional\DataModel\FindTest\RenameTester;
    use \functional\DataModel\FindTest\TypeTester;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\Attribute\Naming\Rename;
    use \Wrapped\_\DataModel\DataModel;

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
    extends \PHPUnit\Framework\TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function tearDown(): void {
            static::$connection->query( "drop table if exists \"RenameTester\" " );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );

            $this->tearDown();
            static::$connection->query( "create table \"RenameTester\" ( id serial, \"rAnDoMCAsIng\" text ) " );
        }

        public function createInstance(): RenameTester {

            (new RenameTester() )->save();

            // restore
            return RenameTester::get( 1 );
        }

        public function test() {
            $this->createInstance();
        }

        public function testUpdate() {

            $instance = $this->createInstance();
            $instance->setRandomCasing( 'test' )->save();

            $this->assertNotNull( RenameTester::findSingle( [ 'randomCasing' => 'test' ] ) );
            $this->assertNotNull( RenameTester::findSingle( [ 'rAnDoMCAsIng' => 'test' ] ) );
        }

    }
