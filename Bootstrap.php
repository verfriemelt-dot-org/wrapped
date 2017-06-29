<?php

    namespace Wrapped;

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

                header( "Content-type: text/plain" );
                echo $e->getTraceAsString() . PHP_EOL . PHP_EOL . PHP_EOL;
                print_r( $e );
                die();
            } );

            set_error_handler( function ( $errno, $errstr, $errfile, $errline ) {
                throw new \ErrorException( $errstr, 0, $errno, $errfile, $errline );
            } );
        }

    }
