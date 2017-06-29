<?php

    namespace www;

    use \Wrapped\_\Cli\Console;
    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Router\Router;
    use \Wrapped\Bootstrap;

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
        "REQUEST_URI", Console::getInstance()->getArgsAsString()
    );

    // run setup functions
//    $setupFunctions = include_once __DIR__ . "/_/setup.php";
//    foreach ( $setupFunctions as $func ) {
//        $func();
//    }


    $router = Router::getInstance( $request );
//    $router->addRoutes( ... include_once __DIR__ . "/_/cliRoutes.php" );

    $route = $router->run();
    $route->runCallback( $request );