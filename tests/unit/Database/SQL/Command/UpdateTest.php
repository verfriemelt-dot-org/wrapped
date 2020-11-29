<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Command\Update;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\Value;

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
            $update->add( new Identifier( 'test' ), (new Value( 1 ) ) );

            $expected = 'UPDATE table SET test = 1';

            $this->assertSame( $expected, $update->stringify() );
        }

        public function testComplex() {

            $update = new Update( new Identifier( 'table' ) );
            $update->add( new Identifier( 'test' ), (new Value( 1 ) ) );
            $update->add(
                new Identifier( 'complex' ),
                (new Expression() )
                    ->add( new Identifier( 'complex' ) )
                    ->add( new Operator( '+' ) )
                    ->add( (new Value( 1 ) ) )
            );

            $expected = 'UPDATE table SET test = 1, complex = complex + 1';

            $this->assertSame( $expected, $update->stringify() );
        }

    }
