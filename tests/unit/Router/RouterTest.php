<?php

    use \verfriemelt\wrapped\_\Exception\Router\NoRoutesPresent;
    use \verfriemelt\wrapped\_\Exception\Router\RouteGotFiltered;
    use \verfriemelt\wrapped\_\Http\Request\Request;
    use \verfriemelt\wrapped\_\Router\Route;
    use \verfriemelt\wrapped\_\Router\RouteGroup;
    use \verfriemelt\wrapped\_\Router\Router;

    class RouterTest
    extends \PHPUnit\Framework\TestCase {

        public function testRouterMatchingSingleRoute() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/test" ] );
            $router  = (new Router() )->addRoutes(
                Route::create( "/test" )
            );

            $this->assertTrue( $router->handleRequest( $request ) instanceof Route );
        }

        public function testRouterEmpty() {

            $this->expectException( NoRoutesPresent::class );
            (new Router() )->handleRequest( new Request() );
        }

        public function testRouteGroup() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/admin/test" ] );
            $router  = (new Router() )->addRoutes(
                RouteGroup::create( "/admin" )
                    ->add( Route::create( "/test" ) )
                    ->add( Route::create( "/test1" ) )
            );

            $this->assertTrue( $router->handleRequest( $request ) instanceof Route );
        }

        public function testRouteVsRouteGroupWeight() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/admin" ] );

            $router = new Router();
            $router->addRoutes( Route::create( "/admin" )->call( function () {
                    return "a";
                } ) );
            $router->addRoutes(
                RouteGroup::create( "/admin" )
                    ->add( Route::create( "/test" )->call( function () {
                            return "b";
                        } ) )
                    ->add( Route::create( "/test1" )->call( function () {
                            return "b";
                        } ) )
            );

            $this->assertEquals( "a", $router->handleRequest( $request )->getCallback()() );
        }

        public function testRouteGroupFirstButNoMatchingChildren() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/admin" ] );

            $router = new Router();
            $router->addRoutes(
                RouteGroup::create( "/admin" )
                    ->add( Route::create( "/test" )->call( function () {
                            return "b";
                        } ) )
                    ->add( Route::create( "/test1" )->call( function () {
                            return "b";
                        } ) )
            );
            $router->addRoutes( Route::create( "/admin" )->call( function () {
                    return "a";
                } ) );

            $this->assertEquals( "a", $router->handleRequest( $request )->getCallback()() );
        }

        public function testNestedRouteGroups() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/api/test/nice" ] );

            $router = new Router();
            $router->addRoutes(
                RouteGroup::create( "/api" )->add(
                    RouteGroup::create( '/test' )->add(
                        Route::create( "/nice" )->call( function () {
                            return "win";
                        } )
                    ),
                    Route::create( ".*" )->call( function () {
                        return "default";
                    } )
                )
            );

            $this->assertEquals( "win", $router->handleRequest( $request )->getCallback()() );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/api/asd" ] );

            $this->assertEquals( "default", $router->handleRequest( $request )->getCallback()() );
        }

        public function testMatchingGroupsWithNoChilds() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/api/win" ] );

            $router = new Router();
            $router->addRoutes(
                RouteGroup::create( "/api" ),
                Route::create( ".*" )->call( function () {
                    return "win";
                } )
            );

            $this->assertEquals( "win", $router->handleRequest( $request )->getCallback()() );
        }

        public function testCapturingRouteData() {
            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/list/geocaches" ] );

            $router = new Router();
            $router->addRoutes(
                RouteGroup::create( "/(?<key>list)" )->add( Route::create( "/(?<key2>geocaches)" )->call( function () {
                        return "win";
                    } ) )
            );

            $route = $router->handleRequest( $request );
            $request->setAttributes( $route->getAttributes() );

            $this->assertTrue( $request->attributes()->has( "key" ) );
            $this->assertTrue( $request->attributes()->has( "key2" ) );
        }

        public function testOptionalRouteGroup() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/test/a" ] );

            $router = new Router();
            $router->addRoutes(
                RouteGroup::create( '(?:/[a-z]{4})?' )->add( Route::create( "/a" )->call( function () {
                        return true;
                    } ) )
            );
            $this->assertTrue( $router->handleRequest( $request )->getCallback()() );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/a" ] );
            $this->assertTrue( $router->handleRequest( $request )->getCallback()() );
        }

        public function testWut() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/th/detail/geocacher/1" ] );

            $router = new Router();
            $router->addRoutes(
                RouteGroup::create( '^(?:/([a-z]{2})(?=/))?' )->add(
                    RouteGroup::create( "(?:/detail)?" )
                        ->add( Route::create( "/geocacher/(?<geocacherId>[0-9]+)" )->call( fn() => true ) )
                )
            );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/th/detail/geocacher/1" ] );
            $result  = $router->handleRequest( $request );

            $this->assertTrue( $result->getCallback()() );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/th/geocacher/1" ] );
            $result  = $router->handleRequest( $request );

            $this->assertTrue( $result->getCallback()() );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/detail/geocacher/1" ] );
            $result  = $router->handleRequest( $request );

            $this->assertTrue( $result->getCallback()() );
        }

    }
