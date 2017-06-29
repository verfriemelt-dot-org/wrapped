<?php

    namespace www;

    use \Exception;
    use \Wrapped\_\Exception\Router\NoRouteMatching;
    use \Wrapped\_\Exception\Router\NoRoutesPresent;
    use \Wrapped\_\Exception\Router\RouteGotFiltered;
    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Http\Response\Response;
    use \Wrapped\_\Router\Router;
    use \Wrapped\Bootstrap;

// define enviroment
    define( "_", true );

    require 'Bootstrap.php';

    Bootstrap::registerAutoloader();
    Bootstrap::registerExceptionHandling();

    // run setup functions
//    $setupFunctions = include_once __DIR__ . "/_/setup.php";
//    foreach ( $setupFunctions as $func ) {
//        $func();
//    }

    $request = Request::getInstance();
    $router  = Router::getInstance( $request );
//    $router->addRoutes( ... include_once __DIR__ . "/_/routes.php" );

    try {
        $route    = $router->run();
        $response = $route->runCallback( $request );

        if ( $response instanceof Response ) {
            $response->send();
        } else {
            throw new Exception( "no response given" );
        }
    } catch ( NoRouteMatching $e ) {

    } catch ( NoRoutesPresent $e ) {
        $res = new Response();
        $res->setStatusCode( 404 );
        $res->setContent( "404 - no routes" );
        $res->send();
    } catch ( RouteGotFiltered $e ) {
        $res = new Response();
        $res->setStatusCode( 403 );
        $res->setContent( "403 - forbidden" );
        $res->send();
    }
