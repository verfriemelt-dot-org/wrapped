<?php

    namespace integration\SQL;

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\Database;
    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\Driver\SQLite;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Select;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Cast;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;
    use \verfriemelt\wrapped\_\Database\SQL\Statement;

    class QueryTest
    extends TestCase {

        static DatabaseDriver $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', SQLite::class, "", "", "", "", 0 );
        }

        public function test() {

            $stmt = new Statement();
            $stmt->setCommand( new Select( (new Expression( new Value( 1 ) ) )->as( new Identifier( 'test' ) ) ) );

            $this->assertSame( "1", static::$connection->run( $stmt )->fetch()['test'] );
        }

    }
