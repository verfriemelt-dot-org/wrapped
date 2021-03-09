<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class ExpressionTest
    extends TestCase {

        public function testInit() {
            new Expression();
        }

        public function testNesting() {

            $exp = new Expression();
            $exp->add(
                (new Expression() )
                    ->add( new Value( true ) )
            );


            $this->assertSame( 'true', $exp->stringify() );
        }

        public function testEmpty() {

            $exp = new Expression();

            $this->expectExceptionObject( new Exception( 'empty' ) );
            $exp->stringify();
        }

    }
