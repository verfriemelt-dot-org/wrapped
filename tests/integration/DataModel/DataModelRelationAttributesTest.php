<?php

    namespace functional;

    use \Exception;
    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\Database;
    use \verfriemelt\wrapped\_\Database\Driver\Postgres;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Relation\OneToOneRelation;
    use \verfriemelt\wrapped\_\DataModel\DataModel;
    use function \Symfony\Component\String\b;

    class A
    extends DataModel {

        public ?int $id = null;

        public ?int $bId = null;

        protected ?b $aObject = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getBId(): ?int {
            return $this->bId;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setBId( ?int $bId ) {
            $this->bId = $bId;
            return $this;
        }

    }

    class B
    extends DataModel {

        public ?int $id = null;

        public ?int $aId = null;

        #[ OneToOneRelation( 'aId', 'id' ) ]
        protected ?a $aObject = null;

        #[ OneToOneRelation( 'aId', 'did' ) ]
        protected ?a $aWrongMarked = null;

        protected ?a $aObjectNotMarked = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getAId(): ?int {
            return $this->aId;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setAId( ?int $aId ) {
            $this->aId = $aId;
            return $this;
        }

    }

    class DataModelRelationAttributesTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
            static::$connection->query( 'create table "A" ( id serial primary key, b_id int );' );
            static::$connection->query( 'create table "B" ( id serial primary key, a_id int );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table if exists "A";' );
            static::$connection->query( 'drop table if exists "B";' );
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
            $this->expectExceptionObject( new Exception( 'attribute' ) );

            b::get( 1 )->aObjectNotMarked();
        }

        public function testResolv() {

            $this->buildObjects();

            $this->assertSame( 1, b::get( 1 )->getAId() );
            $this->assertSame( 1, b::get( 1 )->aObject()->getId() );
        }

//        public function testWrongMarked() {
//
//            $this->buildObjects();
//
////            $this->markTestIncomplete( 'not implemented' );
//            $this->expectExceptionObject( new Exception( 'not translateable' ) );
//
//            b::get( 1 )->aWrongMarked();
//        }
    }
