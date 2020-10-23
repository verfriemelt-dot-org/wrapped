<?php

    namespace functional;

    use \Exception;
    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\DataModel;

    class a
    extends DataModel {

        public ?int $id = null;

        public ?int $bId = null;

        protected ?b $aObject = null;

        public function getId(): int {
            return $this->id;
        }

        public function getBId(): int {
            return $this->bId;
        }

        public function setId( int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setBId( int $bId ) {
            $this->bId = $bId;
            return $this;
        }

    }

    class b
    extends DataModel {

        public ?int $id = null;

        public ?int $aId = null;

        #[ \Wrapped\_\DataModel\Attribute\PropertyResolver('aId', 'id') ]

        protected ?a $aObject = null;

        #[ \Wrapped\_\DataModel\Attribute\PropertyResolver('aId', 'did') ]

        protected ?a $aWrongMarked = null;

        protected ?a $aObjectNotMarked = null;

        public function getId(): int {
            return $this->id;
        }

        public function setId( int $id ) {
            $this->id = $id;
            return $this;
        }

        public function getAId(): ?int {
            return $this->aId;
        }

        public function setAId( ?int $aId ) {
            $this->aId = $aId;
            return $this;
        }

    }

    class FunctionalDataModelResolverAttributeTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
            static::$connection->query( 'create table a ( id serial primary key, bid int );' );
            static::$connection->query( 'create table b ( id serial primary key, aid int );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table if exists a;' );
            static::$connection->query( 'drop table if exists b;' );
        }

        public function buildObjects() {
            (new b() )->save();
            (new a() )->setBId( 1 )->save();

            b::get( 1 )->setAId( 1 )->save();
        }

        public function testInit() {
            $this->buildObjects();
        }

        public function testNotPrepped() {

            $this->buildObjects();
            $this->expectExceptionObject( new Exception( 'Attribute' ) );

            b::get( 1 )->aObjectNotMarked;
        }

        public function testResolv() {

            $this->buildObjects();

            $this->assertSame( 1, b::get( 1 )->getAId() );
            $this->assertSame( 1, b::get( 1 )->aObject->getId() );
        }

        public function testWrongMarked() {

            $this->buildObjects();

            $this->expectExceptionObject( new Exception( 'not translateable' ) );

            b::get( 1 )->aWrongMarked;
        }

    }