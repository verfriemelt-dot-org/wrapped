<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\Database;
    use \verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use \verfriemelt\wrapped\_\Database\Driver\Postgres;
    use \verfriemelt\wrapped\_\Database\Driver\SQLite;
    use \verfriemelt\wrapped\_\Http\ParameterBag;

require_once __DIR__ . '/../vendor/autoload.php';

    $env = new ParameterBag( getenv() );

    if ( !$env->has( 'database_driver' ) ) {

        return;
    }


    switch ( $env->get( 'database_driver' ) ) {
        case 'sqlite':
            Database::createNewConnection( 'default', SQLite::class, "", "", "", "", 0 );
            break;

        case 'postgresql':
            Database::createNewConnection(
                'default',
                Postgres::class,
                $env->get( 'db_user', 'docker' ),
                $env->get( 'db_pass', 'docker' ),
                $env->get( 'db_host', 'localhost' ),
                $env->get( 'db_name', 'docker' ),
                (int) $env->get( 'db_port', 5432 )
            );
            break;

        default:
            die( 'driver not supported' );
    }

    abstract class DatabaseTestCase
    extends TestCase {

        static DatabaseDriver $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::getConnection();
        }

    }
