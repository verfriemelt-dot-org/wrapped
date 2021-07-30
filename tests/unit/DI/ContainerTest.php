<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\DI\Container;

    class a { public function __construct( public b $b ) {} }
    class b { public function __construct( public string $instance = 'number 1' ) {} }

    class circle { public function __construct( public circle $circle ) {} }

    class circleA { public function __construct( public circleB $circle ) {} }
    class circleB { public function __construct( public circleC $circle ) {} }
    class circleC { public function __construct( public circleA $circle ) {} }

    interface i {}
    class a_i implements i {}
    class b_i implements i {}

    class ContainerTest
    extends TestCase {

        public function testCanCreateNewContainer() {
            new Container;
        }

        public function testGetClass() {

            $container = new Container;
            $instance  = $container->get( a::class );

            $this->assertInstanceOf( a::class, $instance );
            $this->assertInstanceOf( b::class, $instance->b );
        }

        public function testShouldReuseInstancesPerDefault() {

            $container = new Container();

            $b = new b( 'number 2' );
            $container->register( $b::class, $b );

            $this->assertSame( $container->get( a::class )->b->instance, $b->instance, 'instance must be reused' );
        }

        public function testDoNotReuseOnWhenConfigured() {

            $container = new Container();

            $b = new b( 'number 2' );
            $container->register( $b::class, $b )->share( false );

            $this->assertNotSame( $container->get( a::class )->b->instance, $b->instance, 'instance must not be reused' );
        }

        public function testShouldThrowExceptionOnCircularDepedencies() {

            $this->expectExceptionMessage( 'circular' );
            $container = new Container;

            $container->get( circleA::class );
        }

        public function testGetInstanceFromInterfaceWhenRegistered() {

            $container = new Container;
            $container->register( a_i::class );

            $this->assertTrue ( $container->get( i::class ) instanceof a_i ) ;
        }

    }
