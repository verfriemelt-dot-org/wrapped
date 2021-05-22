<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\DI\Container;

    class a { public function __construct( public b $b ) {} }
    class b { public function __construct( public string $instance = 'number 1' ) {} }

    class circle { public function __construct( public circle $circle ) {} }

    class circleA { public function __construct( public circleB $circle ) {} }
    class circleB { public function __construct( public circleC $circle ) {} }
    class circleC { public function __construct( public circleA $circle ) {} }



    class ContainerTest
    extends TestCase {

        public function testInstanciate() {
            new Container;
        }

        public function testInstanciateClass() {

            $container = new Container;
            $instance = $container->get( a::class );

            $this->assertInstanceOf( a::class, $instance );
            $this->assertInstanceOf( b::class, $instance->b );

        }

        public function testLiveCycle() {

            $container = new Container();

            $b = new b('number 2');
            $container->register( $b::class, $b );

            $this->assertSame( $container->get( a::class )->b->instance, $b->instance, 'instance must be reused' );

        }

        public function testShouldFail() {

            $this->expectExceptionMessage('circular' );
            $container = new Container;

            $container->get( circle::class );
            
            $container->get( circleA::class );

        }



    }
