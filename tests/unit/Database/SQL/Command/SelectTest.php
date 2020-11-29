<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\Database\SQL\Expression\Operator;

    class SelectTest
    extends TestCase {

        public function testInit() {
            $select = new Select();
        }

        public function testTrue() {
            $select = new Select();
            $select->add( new Value( true ) );

            $this->assertSame( 'SELECT true', $select->stringify() );
        }

        public function testOnePlusOne() {
            $select = new Select();
            $select->add(
                (new Expression() )
                    ->add( (new Value( 1 ) )->useBinding( false ) )
                    ->add( new Operator( '+' ) )
                    ->add( (new Value( 3 ) )->useBinding( false ) )
            );

            $this->assertSame( 'SELECT 1 + 3', $select->stringify() );
        }

    }
