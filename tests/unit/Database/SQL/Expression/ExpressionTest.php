<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Primitive;

    class ExpressionTest
    extends TestCase {

        public function testInit() {
            new Expression();
        }

        public function testNesting() {

            $exp = new Expression();
            $exp->add(
                (new Expression() )
                    ->add( new Primitive( true ) )
            );


            $this->assertSame( 'true', $exp->stringify() );
        }

        public function testEmpty() {

            $exp = new Expression();

            $this->expectExceptionObject( new Exception( 'empty' ) );
            $exp->stringify();
        }

    }