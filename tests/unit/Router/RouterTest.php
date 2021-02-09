<?php

    use \Wrapped\_\Exception\Router\NoRoutesPresent;
    use \Wrapped\_\Exception\Router\RouteGotFiltered;
    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Router\Route;
    use \Wrapped\_\Router\RouteGroup;
    use \Wrapped\_\Router\Router;

    class RouterTest
    extends \PHPUnit\Framework\TestCase {

        public function tearDown(): void {
            Router::destroy();
            Request::destroy();
        }

        public function testCanBeInstantiated() {
            $this->assertTrue(
                Router::getInstance() instanceof Router
            );
        }

        public function testAddingRoutes() {
            Router::getInstance()->addRoutes(
                Route::create( "/" )
            );

            $this->assertSame( 1, Router::getInstance()->count() );
        }

        public function testRouterMatchingSingleRoute() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/test" ] );

            $router = Router::getInstance( $request )->addRoutes(
                Route::create( "/test" )
            );

            $this->assertTrue( $router->run() instanceof Route );
            $router;
        }

        public function testRouterEmpty() {

            $this->expectException( NoRoutesPresent::class );
            Router::getInstance()->run();
        }

        public function testRouteFiltered() {

            $this->expectException( RouteGotFiltered::class );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/test" ] );
            $router  = Router::getInstance( $request )->addRoutes(
                Route::create( "/test" )->setFilterCallback( function () {
                    return true;
                } )
            );

            $router->run();
        }

        public function testRouteGroup() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/admin/test" ] );
            $router  = Router::getInstance( $request )->addRoutes(
                RouteGroup::create( "/admin" )
                    ->add( Route::create( "/test" ) )
                    ->add( Route::create( "/test1" ) )
            );

            $this->assertTrue( $router->run() instanceof Route );
        }

        public function testRouteVsRouteGroupWeight() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/admin" ] );

            $router = Router::getInstance( $request );
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

            $this->assertEquals( "a", $router->run()->runCallback( $request ) );
        }

        public function testRouteGroupFirstButNoMatchingChildren() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/admin" ] );

            $router = Router::getInstance( $request );
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

            $this->assertEquals( "a", $router->run()->runCallback( $request ) );
        }

        public function testNestedRouteGroups() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/api/test/nice" ] );

            $router = Router::getInstance( $request );
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

            $this->assertEquals( "win", $router->run()->runCallback( $request ) );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/api/asd" ] );
            $router->setRequest( $request );

            $this->assertEquals( "default", $router->run()->runCallback( $request ) );
        }

        public function testMatchingGroupsWithNoChilds() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/api/win" ] );

            $router = Router::getInstance( $request );
            $router->addRoutes(
                RouteGroup::create( "/api" ),
                Route::create( ".*" )->call( function () {
                    return "win";
                } )
            );

            $this->assertEquals( "win", $router->run()->runCallback( $request ) );
        }

        public function testCapturingRouteData() {
            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/list/geocaches" ] );

            $router = Router::getInstance( $request );
            $router->addRoutes(
                RouteGroup::create( "/(?<key>list)" )->add( Route::create( "/(?<key2>geocaches)" )->call( function () {
                        return "win";
                    } ) )
            );

            $router->run();

            $this->assertTrue( $request->attributes()->has( "key" ) );
            $this->assertTrue( $request->attributes()->has( "key2" ) );
        }

        public function testOptionalRouteGroup() {

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/test/a" ] );

            $router = Router::getInstance();
            $router->addRoutes(
                RouteGroup::create( '(?:/[a-z]{4})?' )->add( Route::create( "/a" )->call( function () {
                        return true;
                    } ) )
            );
            $this->assertTrue( $router->run( $request )->runCallback( $request ) );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/a" ] );
            $this->assertTrue( $router->run( $request )->runCallback( $request ) );
        }

        public function testWut() {

            $router = new Router();

            $router->addRoutes(
                RouteGroup::create( '^(?:/([a-z]{2})(?=/))?' )->add(
                    RouteGroup::create( "(?:/detail)?" )
                        ->add( Route::create( "/geocacher/(?<geocacherId>[0-9]+)" )->call( fn() => true ) )
                )
            );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/th/detail/geocacher/1" ] );
            $result = $router->run( $request );

            $this->assertTrue ( $result->runCallback( $request ) );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/th/geocacher/1" ] );
            $result = $router->run( $request );

            $this->assertTrue ( $result->runCallback( $request ) );

            $request = new Request( [], [], [], [], [], [ "REQUEST_URI" => "/detail/geocacher/1" ] );
            $result = $router->run( $request );

            $this->assertTrue ( $result->runCallback( $request ) );
        }

    }
