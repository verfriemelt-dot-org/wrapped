<?php

    namespace verfriemelt\wrapped;

    use ErrorException;
    use verfriemelt\wrapped\_\Cli\Console;

    class Bootstrap {

        CONST NAMESPACE = "verfriemelt\\wrapped";

        static public function registerAutoloader() {

            spl_autoload_register( function ( $class ) {

                if ( substr( $class, 0, strlen( self::NAMESPACE ) ) !== self::NAMESPACE ) {
                    return false;
                }

                $class        = substr( $class, strlen( self::NAMESPACE ) );
                $possiblePath = __DIR__ . "/" . str_replace( "\\", "/", $class ) . ".php";

                if ( file_exists( $possiblePath ) ) {
                    return require_once $possiblePath;
                }
            } );
        }

        static public function registerExceptionHandling() {

            // lazy ass exception handling
            set_exception_handler( function ( $e ) {

                if ( !Console::isCli() ) {
//                    header( "Content-type: text/plain" );
                }

                $trace = $e->getTraceAsString() . PHP_EOL . PHP_EOL . PHP_EOL . print_r( $e, 1 );
                $trace .= PHP_EOL . PHP_EOL;
                $trace .= "SERVER:";
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
