<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Command\Values;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\Value;

    class ValuesTest
    extends TestCase {

        public function testInit() {
            $select = new Values( );
        }

        public function testTrue() {
            $select = new Values();
            $select->add( new Value( true ) );

            $this->assertSame( 'VALUES ( true )', $select->stringify() );
        }

        public function testOnePlusOne() {
            $select = new Values();
            $select->add(
                (new Expression() )
                    ->add( (new Value( 1 ) )->useBinding( false ) )
                    ->add( new Operator( '+' ) )
                    ->add( (new Value( 3 ) )->useBinding( false ) )
            );

            $this->assertSame( 'VALUES ( 1 + 3 )', $select->stringify() );
        }

    }
