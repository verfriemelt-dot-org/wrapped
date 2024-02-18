<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Router;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\ArgumentMetadataFactory;
use verfriemelt\wrapped\_\DI\ArgumentResolver;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Router\Exception\NoRouteMatching;
use verfriemelt\wrapped\_\Http\Router\Route;
use verfriemelt\wrapped\_\Http\Router\RouteGroup;
use verfriemelt\wrapped\_\Http\Router\Router;
use Override;

class RouterTest extends TestCase
{
    private Router $router;
    private Container $container;

    #[Override]
    public function setUp(): void
    {
        $this->router = new Router(
            new ArgumentResolver(
                $this->container = new Container(),
                new ArgumentMetadataFactory()
            )
        );
    }

    public function test_router_empty(): void
    {
        $this->expectException(NoRouteMatching::class);

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/']);
        $this->router->handleRequest($request);
    }

    public function test_route_vs_route_group_weight(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/admin']);

        $this->router->add(
            Route::create('/admin')->call(fn () => 'a')
        );
        $this->router->add(
            RouteGroup::create('/admin')
                ->add(
                    Route::create('/test')->call(fn () => 'b')
                )
                ->add(
                    Route::create('/test1')->call(fn () => 'b')
                )
        );

        $result = $this->router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('a', $result());
    }

    public function test_route_group_first_but_no_matching_children(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/admin']);

        $this->router->add(
            RouteGroup::create('/admin')
                ->add(
                    Route::create('/test')->call(fn () => 'b')
                )
                ->add(
                    Route::create('/test1')->call(fn () => 'b')
                )
        );
        $this->router->add(
            Route::create('/admin')->call(fn () => 'a')
        );

        $result = $this->router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('a', $result());
    }

    public function test_nested_route_groups(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/test/nice']);

        $this->router->add(
            RouteGroup::create('/api')->add(
                RouteGroup::create('/test')->add(
                    Route::create('/nice')->call(fn () => 'win')
                ),
                Route::create('.*')->call(fn () => 'default')
            )
        );

        $result = $this->router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('win', $result());

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/asd']);

        $result = $this->router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('default', $result());
    }

    public function test_matching_groups_with_no_childs(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/win']);

        $this->router->add(
            RouteGroup::create('/api'),
            Route::create('.*')->call(fn () => 'win')
        );

        $result = $this->router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('win', $result());
    }

    public function test_capturing_route_data(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/list/geocaches']);

        $this->router->add(
            RouteGroup::create('/(?<key>list)')->add(
                Route::create('/(?<key2>geocaches)')->call(fn () => 'win')
            )
        );

        $route = $this->router->handleRequest($request);
        $request->setAttributes($route->getAttributes());

        static::assertTrue($request->attributes()->has('key'));
        static::assertTrue($request->attributes()->has('key2'));
    }

    public function test_optional_route_group(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/test/a']);

        $this->router->add(
            RouteGroup::create('(?:/[a-z]{4})?')->add(
                Route::create('/a')->call(fn () => true)
            )
        );

        $result = $this->router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame(true, $result());

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/a']);
        $result = $this->router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
    }

    public function test_wut(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/th/detail/geocacher/1']);

        $this->router->add(
            RouteGroup::create('^(?:/([a-z]{2})(?=/))?')->add(
                RouteGroup::create('(?:/detail)?')
                    ->add(Route::create('/geocacher/(?<geocacherId>[0-9]+)')->call(fn () => true))
            )
        );

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/th/detail/geocacher/1']);
        $result = $this->router->handleRequest($request);

        static::assertTrue(is_callable($result->getCallback()));

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/th/geocacher/1']);
        $result = $this->router->handleRequest($request);

        static::assertTrue(is_callable($result->getCallback()));

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/detail/geocacher/1']);
        $result = $this->router->handleRequest($request);

        static::assertTrue(is_callable($result->getCallback()));
    }

    public function test_filter_seeing_attributes(): void
    {
        $this->router->add(
            Route::create('/(foo)')->addFilter(function (Request $request): false {
                static::assertSame(
                    'foo',
                    $request->attributes()->first()
                );

                return false;
            })
        );

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/foo']);
        $this->container->register($request::class, $request);
        $this->router->handleRequest($request);
    }
}
