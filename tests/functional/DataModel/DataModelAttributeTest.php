<?php

    namespace functional\DataModel\AttributeTest;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\DataModel;

    #[\Wrapped\_\DataModel\Attribute\Naming\LowerCase]
    class LowerDummy
    extends DataModel {

        public ?int $id = null;

        #[\Wrapped\_\DataModel\Attribute\Naming\LowerCase]

        public ?string $complexFieldName = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getComplexFieldName(): ?string {
            return $this->complexFieldName;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setComplexFieldName( ?string $complexFieldName ) {
            $this->complexFieldName = $complexFieldName;
            return $this;
        }

    }

    #[\Wrapped\_\DataModel\Attribute\Naming\CamelCase]

    class CamelCaseDummy
    extends LowerDummy {

        #[\Wrapped\_\DataModel\Attribute\Naming\CamelCase]

        public ?string $complexFieldName = null;

    }

    #[\Wrapped\_\DataModel\Attribute\Naming\SnakeCase]

    class SnakeCaseDummy
    extends LowerDummy {

        #[\Wrapped\_\DataModel\Attribute\Naming\SnakeCase]

        public ?string $complexFieldName = null;

    }

    class DataModelAttributeTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table if exists dummy;' );
            static::$connection->query( 'drop table if exists lowerdummy ;' );
            static::$connection->query( 'drop table if exists snake_case_dummy;' );
            static::$connection->query( 'drop table if exists "camelCaseDummy";' );
        }

        public function saveInstance( $class, $name = 'test' ) {

            $obj = new $class;
            $obj->setComplexFieldName( $name );
            $obj->save();

            return $obj;
        }

        public function testallLower() {
            static::$connection->query( 'create table lowerdummy ( id serial primary key, complexfieldname text );' );
            $this->saveInstance( LowerDummy::class, 'test' );

            $this->assertNotNull( LowerDummy::findSingle( [ 'complexfieldname' => 'test' ] ) );
            $this->assertNotNull( LowerDummy::findSingle( [ 'complexFieldName' => 'test' ] ) );
        }

        public function testCamelCase() {
            static::$connection->query( 'create table "camelCaseDummy" ( id serial primary key, "complexFieldName" text );' );
            $this->saveInstance( CamelCaseDummy::class, 'test' );

            $this->assertNotNull( CamelCaseDummy::findSingle( [ 'complexFieldName' => 'test' ] ) );
        }

        public function testSnakeCase() {

            static::$connection->query( 'create table snake_case_dummy ( id serial primary key, complex_field_name text );' );
            $this->saveInstance( SnakeCaseDummy::class, 'test' );

            $this->assertNotNull( SnakeCaseDummy::findSingle( [ 'complex_field_name' => 'test' ] ) );
            $this->assertNotNull( SnakeCaseDummy::findSingle( [ 'complexFieldName' => 'test' ] ) );

            $this->assertSame( 'test', SnakeCaseDummy::findSingle( [ 'complex_field_name' => 'test' ] )->getComplexFieldName() );
        }

    }
