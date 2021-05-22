<?php

    namespace www;

    use \verfriemelt\wrapped\_\Cli\Console;
    use \verfriemelt\wrapped\_\Http\Request\Request;
    use \verfriemelt\wrapped\_\Router\Router;
    use \verfriemelt\wrapped\Bootstrap;

    define( "_", true );

    require 'Bootstrap.php';

    Bootstrap::registerAutoloader();
    Bootstrap::registerExceptionHandling();

    //

    if ( !Console::isCli() ) {
        die( "nope" );
    }

    // setup request object
    $request = Request::getInstance();
    $request->server()->override(
        "REQUEST_URI", Console::getInstance()
    );

    // run setup functions
//    $setupFunctions = include_once __DIR__ . "/_/setup.php";
//    foreach ( $setupFunctions as $func ) {
//        $func();
//    }


    $router = Router::getInstance( $request );
//    $router->addRoutes( ... include_once __DIR__ . "/_/cliRoutes.php" );

    $route = $router->handleRequest();
    $route->runCallback( $request );
