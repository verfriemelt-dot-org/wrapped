<?php

    namespace functional\DataModel\AttributeTest;

    use \functional\DataModel\AttributeTest\CamelCaseDummy;
    use \functional\DataModel\AttributeTest\LowerDummy;
    use \functional\DataModel\AttributeTest\SnakeCaseDummy;
    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\Database;
    use \verfriemelt\wrapped\_\Database\Driver\SQLite;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\CamelCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;
    use \verfriemelt\wrapped\_\DataModel\DataModel;

    #[ LowerCase ]
    class LowerDummy
    extends DataModel {

        public ?int $id = null;

        #[ LowerCase ]
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

    #[ CamelCase ]
    class CamelCaseDummy
    extends LowerDummy {

        #[ CamelCase ]
        public ?string $complexFieldName = null;

    }

    #[ SnakeCase ]
    class SnakeCaseDummy
    extends LowerDummy {

        #[ SnakeCase ]
        public ?string $complexFieldName = null;

    }

    class DataModelAttributeTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', SQLite::class, "", "", "", "", 0 );
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
