<?php

    namespace functional;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\TablenameOverride;

    class TypedDummy
    extends DataModel
    implements TablenameOverride {

        public ?int $id = null;

        public ?string $name = null;

        public ?\Wrapped\_\DateTime\DateTime $pubtime = null;

        public $untyped;

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
            return 'dummy';
        }

        public function getPubtime(): ?\Wrapped\_\DateTime\DateTime {
            return $this->pubtime;
        }

        public function setPubtime( ?\Wrapped\_\DateTime\DateTime $pubtime ) {
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


    }

    class FunctionalDataModelTypedPropertiesTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
            static::$connection->query( 'create table dummy ( id serial primary key, name text, pubtime timestamp, untyped text );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table dummy ;' );
        }

        public function testSave() {
            $test = new TypedDummy();
            $test->setPubtime( new \Wrapped\_\DateTime\DateTime );
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
            $this->assertTrue( is_object( $data->getPubtime() ) );
        }

    }
