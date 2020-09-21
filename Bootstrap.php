<?php

    namespace Wrapped;

    use \ErrorException;
    use \Wrapped\_\Cli\Console;
    use \Wrapped\_\EnvironmentDetector;

    class Bootstrap {

        static public function registerAutoloader() {
            spl_autoload_register( function ( $class ) {

                if ( substr( $class, 0, 7 ) !== "Wrapped" ) {
                    return false;
                }

                $class        = substr( $class, 8 );
                $possiblePath = __DIR__ . "/" . str_replace( "\\", "/", $class ) . ".php";

                if ( file_exists( $possiblePath ) ) {
                    return require_once $possiblePath;
                }
            } );
        }

        static public function registerExceptionHandling() {

            // lazy ass exception handling
            set_exception_handler( function ( $e ) {

                // bail out when on live
                if ( EnvironmentDetector::is( "live" ) )
                    die( "ein fehler. bad news!" );

                if ( !Console::isCli() ) {
                    header( "Content-type: text/plain" );
                }

                $trace = $e->getTraceAsString() . PHP_EOL . PHP_EOL . PHP_EOL . print_r( $e, 1 );
                $trace .= PHP_EOL . PHP_EOL ;
                $trace .= "SERVER:" ;
                $trace .= PHP_EOL;

                $trace .= print_r( $_SERVER, 1 );

                $trace .= PHP_EOL . PHP_EOL;
                $trace .= "GET:";
                $trace .= PHP_EOL;

                $trace .= print_r( $_GET, 1 );

                $trace .= PHP_EOL . PHP_EOL;
                $trace .= "POST:";
                $trace .= PHP_EOL;
                $trace .= print_r( $_POST, 1 );

                echo $trace;

                die();
            } );

            set_error_handler( function ( $errno, $errstr, $errfile, $errline ) {
                throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
            } );
        }

    }
