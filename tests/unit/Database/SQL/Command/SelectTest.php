<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression;
    use \Wrapped\_\Database\SQL\Operator;
    use \Wrapped\_\Database\SQL\Primitives;

    class SelectTest
    extends TestCase {

        public function testInit() {
            $select = new Select();
        }

        public function testTrue() {
            $select = new Select();
            $select->add( new Primitives( 'true' ) );

            $this->assertSame( 'SELECT true', $select->stringify() );
        }

        public function testOnePlusOne() {
            $select = new Select();
            $select->add(
                (new Expression() )
                    ->add( new Primitives( 1 ) )
                    ->add( new Operator( '+' ) )
                    ->add( new Primitives( 1 ) )
            );

            $this->assertSame( 'SELECT 1 + 1', $select->stringify() );
        }

    }
