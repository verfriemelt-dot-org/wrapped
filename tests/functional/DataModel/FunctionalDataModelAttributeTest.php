<?php

    namespace functional;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\TablenameOverride;

    class LowerDummy
    extends DataModel
    implements TablenameOverride {

        public ?int $id = null;

        public ?string $complexFieldName = null;

        public static function fetchTablename(): string {
            return 'dummy';
        }

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

    class FunctionDataModelAttributeTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table dummy;' );
        }

        public function saveInstance( $class, $name = 'test' ) {

            $obj = new $class;
            $obj->setComplexFieldName( $name );
            $obj->save();

            return $obj;
        }

        public function testallLower() {
            static::$connection->query( 'create table dummy ( id serial primary key, complexFieldName text );' );
            $this->saveInstance( LowerDummy::class, 'test' );
        }

    }
