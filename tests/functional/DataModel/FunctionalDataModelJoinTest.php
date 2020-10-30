<?php

    namespace functional\join;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\Database\Facade\JoinBuilder;
    use \Wrapped\_\DataModel\DataModel;

    class A
    extends DataModel {

        public ?int $id = null;

        #[\Wrapped\_\DataModel\Attribute\Naming\SnakeCase]

        public ?int $bId = null;

        #[\Wrapped\_\DataModel\Attribute\PropertyResolver('bId', 'id')]

        protected ?B $b = null;

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

        #[\Wrapped\_\DataModel\Attribute\Naming\SnakeCase]

        public ?int $aId = null;

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

        public static function fetchPredefinedJoins( string $class ): ?callable {

            return [
                C::class => function ( JoinBuilder $j ) {
                    return $j->on( [ 'B', 'id' ], [ 'C', 'bId' ] );
                }
            ][$class];
        }

    }

    class C
    extends DataModel {

        public ?int $id = null;

        #[\Wrapped\_\DataModel\Attribute\Naming\SnakeCase]

        public ?int $bId = null;

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

    class FunctionalDataModelResolverAttributeTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );

            $this->tearDown();

            static::$connection->query( 'create table "A" ( id serial primary key, b_id int );' );
            static::$connection->query( 'create table "B" ( id serial primary key, a_id int );' );
            static::$connection->query( 'create table "C" ( id serial primary key, b_id int );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table if exists "A";' );
            static::$connection->query( 'drop table if exists "B";' );
            static::$connection->query( 'drop table if exists "C";' );
        }

        public function testJoining() {

            (new A() )->save();
            (new A() )->save();
            (new A() )->save();
            (new A() )->setBId( (new B() )->save()->getId() )->save();
            (new A() )->setBId( (new B() )->save()->getId() )->save();

            (new C() )->setBId( B::last()->getId() )->save();

            $query = A::with( new B, function ( JoinBuilder $j ) {
                    return $j->on( [ 'A', 'bId' ], [ 'B', 'id' ] );
                } );

            $query->with( new C );

            $result = $query->get();

            $this->assertSame( 1, $result->count() );
            $this->assertInstanceOf( B::class, $result[0]->b() );
        }

    }
