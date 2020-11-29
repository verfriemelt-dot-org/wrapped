<?php

    namespace functional\SQL;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\Database\SQL\Statement;
    use \Wrapped\_\DateTime\DateTime;

    class ValueTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
        }

        public function test() {

            $time = new DateTime;

            $tests = [
//                "1"                              => 1,
//                "5"                              => "5",
                null                             => null,
//                "false"                          => false,
//                "true"                           => true,
//                "{}"                             => [],
//                "{1,2,3}"                        => [ 1, 2, 3 ],
//                "{'1','2','3'}"                  => [ "1", "2", "3" ],
//                "'{$time->dehydrateToString()}'" => $time,
//                "{true}"                         => [ true ]
            ];

            foreach ( $tests as $exp => $input ) {

                $stmt = new Statement();
                $stmt->setCommand( new Select( (new Value( $input ) )->as( new Identifier( 'result' ) ) ) );



                $result = static::$connection->run( $stmt )->fetchAll()[0]['result'];

//                codecept_debug( gettype( $result ) );
//                codecept_debug( gettype( $exp ) );
//                codecept_debug( gettype( $input ) );
//
//                codecept_debug( $stmt->stringify() );
//                codecept_debug( $stmt->stringify( static::$connection ) );

//                var_dump($result); die();

//                $this->assertSame( $exp, $result );
            }
        }

        public function testNullHandling() {

            $res = static::$connection->connectionHandle->query( "SELECT null" );
            $this->assertSame( null, $res->fetchAll()[0][0] );

            $stmt = new Statement();
            $stmt->setCommand( new Select( (new Value( null ) )->as( new Identifier( 'result' ) ) ) );

            $result = static::$connection->run( $stmt )->fetchAll()[0]['result'];

            $this->assertSame( null, $result );
        }

    }
