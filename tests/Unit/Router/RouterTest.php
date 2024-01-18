<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Router;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Router\Exception\NoRoutesPresent;
use verfriemelt\wrapped\_\Http\Router\Route;
use verfriemelt\wrapped\_\Http\Router\RouteGroup;
use verfriemelt\wrapped\_\Http\Router\Router;

class RouterTest extends TestCase
{
    public function test_router_empty(): void
    {
        $this->expectException(NoRoutesPresent::class);
        (new Router())->handleRequest(new Request());
    }

    public function test_route_vs_route_group_weight(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/admin']);

        $router = new Router();
        $router->add(
            Route::create('/admin')->call(fn () => 'a')
        );
        $router->add(
            RouteGroup::create('/admin')
                ->add(
                    Route::create('/test')->call(fn () => 'b')
                )
                ->add(
                    Route::create('/test1')->call(fn () => 'b')
                )
        );

        $result = $router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('a', $result());
    }

    public function test_route_group_first_but_no_matching_children(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/admin']);

        $router = new Router();
        $router->add(
            RouteGroup::create('/admin')
                ->add(
                    Route::create('/test')->call(fn () => 'b')
                )
                ->add(
                    Route::create('/test1')->call(fn () => 'b')
                )
        );
        $router->add(
            Route::create('/admin')->call(fn () => 'a')
        );

        $result = $router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('a', $result());
    }

    public function test_nested_route_groups(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/test/nice']);

        $router = new Router();
        $router->add(
            RouteGroup::create('/api')->add(
                RouteGroup::create('/test')->add(
                    Route::create('/nice')->call(fn () => 'win')
                ),
                Route::create('.*')->call(fn () => 'default')
            )
        );

        $result = $router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('win', $result());

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/asd']);

        $result = $router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('default', $result());
    }

    public function test_matching_groups_with_no_childs(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/win']);

        $router = new Router();
        $router->add(
            RouteGroup::create('/api'),
            Route::create('.*')->call(fn () => 'win')
        );

        $result = $router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame('win', $result());
    }

    public function test_capturing_route_data(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/list/geocaches']);

        $router = new Router();
        $router->add(
            RouteGroup::create('/(?<key>list)')->add(
                Route::create('/(?<key2>geocaches)')->call(fn () => 'win')
            )
        );

        $route = $router->handleRequest($request);
        $request->setAttributes($route->getAttributes());

        static::assertTrue($request->attributes()->has('key'));
        static::assertTrue($request->attributes()->has('key2'));
    }

    public function test_optional_route_group(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/test/a']);

        $router = new Router();
        $router->add(
            RouteGroup::create('(?:/[a-z]{4})?')->add(
                Route::create('/a')->call(fn () => true)
            )
        );

        $result = $router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
        static::assertSame(true, $result());

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/a']);
        $result = $router->handleRequest($request)->getCallback();
        static::assertTrue(is_callable($result));
    }

    public function test_wut(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/th/detail/geocacher/1']);

        $router = new Router();
        $router->add(
            RouteGroup::create('^(?:/([a-z]{2})(?=/))?')->add(
                RouteGroup::create('(?:/detail)?')
                    ->add(Route::create('/geocacher/(?<geocacherId>[0-9]+)')->call(fn () => true))
            )
        );

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/th/detail/geocacher/1']);
        $result = $router->handleRequest($request);

        static::assertTrue(is_callable($result->getCallback()));

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/th/geocacher/1']);
        $result = $router->handleRequest($request);

        static::assertTrue(is_callable($result->getCallback()));

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/detail/geocacher/1']);
        $result = $router->handleRequest($request);

        static::assertTrue(is_callable($result->getCallback()));
    }
}
