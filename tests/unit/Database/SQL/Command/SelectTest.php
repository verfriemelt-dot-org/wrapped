<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Select;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Operator;

    class SelectTest
    extends TestCase {

        public function testTrue() {
            $select = new Select();
            $select->add( new Value( true ) );

            $this->assertSame( 'SELECT true', $select->stringify() );
        }

        public function testOnePlusOne() {
            $select = new Select();
            $select->add(
                (new Expression() )
                    ->add( (new Value( 1 ) ) )
                    ->add( new Operator( '+' ) )
                    ->add( (new Value( 3 ) ) )
            );

            $this->assertSame( 'SELECT 1 + 3', $select->stringify() );
        }

    }
