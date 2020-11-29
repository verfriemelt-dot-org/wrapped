<?php

    namespace functional\SQL;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Cast;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\SqlFunction;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\Database\SQL\Statement;

    class QueryTest
    extends TestCase {

        static DatabaseDriver $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
        }

        public function test() {

            $stmt = new Statement();
            $stmt->setCommand( new Select( (new Expression( new Value( 1 ), new Cast( 'int' ) ) )->as( new Identifier( 'test' ) ) ) );

            $this->assertSame( 1, static::$connection->run( $stmt )->fetch()['test'] );
        }

    }
