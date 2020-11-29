<?php

    namespace functional\DataModel\FindTest;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\DataModel;

    class TypeTester
    extends DataModel {

        public ?int $id = null;

        public int $aInt = 1;

        public float $aFloat = 1.337;

        public string $aString = 'test';

        public bool $aBool = true;

        public ?int $aNull = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getAInt(): int {
            return $this->aInt;
        }

        public function getAFloat(): float {
            return $this->aFloat;
        }

        public function getAString(): string {
            return $this->aString;
        }

        public function getABool(): bool {
            return $this->aBool;
        }

        public function getANull(): ?int {
            return $this->aNull;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setAInt( int $aInt ) {
            $this->aInt = $aInt;
            return $this;
        }

        public function setAFloat( float $aFloat ) {
            $this->aFloat = $aFloat;
            return $this;
        }

        public function setAString( string $aString ) {
            $this->aString = $aString;
            return $this;
        }

        public function setABool( bool $aBool ) {
            $this->aBool = $aBool;
            return $this;
        }

        public function setANull( ?int $aNull ) {
            $this->aNull = $aNull;
            return $this;
        }

    }

    class DataModelTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function tearDown(): void {
            static::$connection->query( "drop table if exists \"TypeTester\" " );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );

            $this->tearDown();
            static::$connection->query( "create table \"TypeTester\" ( id serial, a_int int, a_float numeric, a_string text, a_bool bool, a_null int ) " );
        }

        public function createInstance() {

            (new TypeTester() )->save();

            // restore

            TypeTester::get( 1 );
        }

        public function test() {
            $this->createInstance();
        }

        public function testFind() {

            // non existing
            $this->assertNull( TypeTester::findSingle( [ "id" => 1 ] ) );


            $this->createInstance();

            // existing
            $this->assertNotNull( TypeTester::findSingle( [ "id" => 1 ] ) );
            $this->assertNotNull( TypeTester::findSingle( [ "aFloat" => 1.337 ] ) );
            $this->assertNotNull( TypeTester::findSingle( [ "aString" => 'test' ] ) );
            $this->assertNotNull( TypeTester::findSingle( [ "aBool" => true ] ) );
            $this->assertNotNull( TypeTester::findSingle( [ "aNull" => null ] ) );
        }

    }
