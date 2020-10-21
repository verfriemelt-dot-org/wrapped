<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Expression\Primitive;

    class PrimitivesTest
    extends TestCase {

        public function testTrue() {
            $primitive = new Primitive( true );
            $this->assertSame( 'true', $primitive->stringify() );
        }

        public function testFalse() {
            $primitive = new Primitive( false );
            $this->assertSame( 'false', $primitive->stringify() );
        }

        public function testNull() {
            $primitive = new Primitive( null );
            $this->assertSame( 'null', $primitive->stringify() );
        }

        public function testOther() {

            $this->expectExceptionObject(new \Exception('not'));
            $primitive = new Primitive( 1 );

        }

    }
