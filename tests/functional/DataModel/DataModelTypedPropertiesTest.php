<?php

    namespace functional;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\TablenameOverride;
    use \Wrapped\_\DateTime\DateTime;
    use \Wrapped\_\DataModel\Attribute\Naming\LowerCase;

    class TypedDummy
    extends DataModel
    implements TablenameOverride {

        public ?int $id = null;

        public ?string $name;

        public ?DateTime $pubtime = null;

        public $untyped;

        #[LowerCase]
        public ?DateTime $lastFoundDate = null;

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

        public static function fetchTablename(): string {
            return 'Dummy';
        }

        public function getPubtime(): ?DateTime {
            return $this->pubtime;
        }

        public function setPubtime( ?DateTime $pubtime ) {
            $this->pubtime = $pubtime;
            return $this;
        }

        public function getUntyped() {
            return $this->untyped;
        }

        public function setUntyped( $untyped ) {
            $this->untyped = $untyped;
            return $this;
        }

        public function getLastFoundDate(): ?DateTime {
            return $this->lastFoundDate;
        }

        public function setLastFoundDate( ?DateTime $lastFoundDate ) {
            $this->lastFoundDate = $lastFoundDate;
            return $this;
        }

    }

    class DataModelTypedPropertiesTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
            static::$connection->query( 'create table "Dummy" ( id serial primary key, name text, pubtime timestamp, untyped text, lastfounddate timestamp );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table "Dummy" ;' );
        }

        public function testSave() {
            $test = new TypedDummy();
            $test->setPubtime( new DateTime );
            $test->save();

            // read
            $data = TypedDummy::get( 1 );
            $this->assertTrue( is_object( $data->getPubtime() ) );
        }

        public function testSaveWithNull() {
            $test = new TypedDummy();
            $test->save();

            // read
            $data = TypedDummy::get( 1 );
            $this->assertTrue( $data->getPubtime() === null );
        }

        public function testReloadWithTime() {
            $test = new TypedDummy(); 
            $test->save();

            $secondInstance = TypedDummy::last();

            $this->assertEquals( $test->getId(), $secondInstance->getId() );

            $this->assertNull( $test->getLastFoundDate() );
            $this->assertNull( $secondInstance->getLastFoundDate() );

            $test->setLastFoundDate( new DateTime( '2012-07-08 11:14:15.889342' ) );
            $test->save();

            // read updated value
            $this->assertNotNull( $secondInstance->reload()->getLastFoundDate(), 'should be updated with datetime' );
            $this->assertEquals( $secondInstance->getLastFoundDate()->toSqlFormat(), '2012-07-08 11:14:15.889342' );

            $test->setLastFoundDate( null );
            $test->save();

            $test->reload();
            $this->assertNull( $test->getLastFoundDate(), 'original is null' );


            // read updated value
            $this->assertNull( $secondInstance->reload()->getLastFoundDate(), 'should be updated to null again' );
        }

    }
