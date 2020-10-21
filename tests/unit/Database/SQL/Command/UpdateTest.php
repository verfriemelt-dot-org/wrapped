<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Command\Update;
    use \Wrapped\_\Database\SQL\Identifier;
    use \Wrapped\_\Database\SQL\Primitives;

    class UpdateTest
    extends TestCase {

        public function testInit() {
            $update = new Update( new Identifier( 'table' ) );
        }

        public function testEmptyStatement() {
            $update = new Update( new Identifier( 'table' ) );

            $this->expectExceptionObject( new Exception( 'empty' ) );
            $update->stringify();
        }

        public function testSimple() {
            $update = new Update( new Identifier( 'table' ) );
            $update->add( new Identifier( 'test' ), new Primitives( 1 ) );

            $expected = 'UPDATE table SET test = 1';

            $this->assertSame( $expected, $update->stringify() );
        }

        public function testComplex() {

            $update = new Update( new Identifier( 'table' ) );
            $update->add( new Identifier( 'test' ), new Primitives( 1 ) );
            $update->add(
                new Identifier( 'complex' ),
                (new \Wrapped\_\Database\SQL\Expression() )
                    ->add( new Identifier( 'complex' ) )
                    ->add( new \Wrapped\_\Database\SQL\Operator( '+' ) )
                    ->add( new Primitives( 1 ) )
            );

            $expected = 'UPDATE table SET test = 1, complex = complex + 1';

            $this->assertSame( $expected, $update->stringify() );
        }

    }
