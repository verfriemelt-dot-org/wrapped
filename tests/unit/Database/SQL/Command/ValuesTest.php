<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Select;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Values;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class ValuesTest
    extends TestCase {

        public function testTrue(): void {
            $select = new Values();
            $select->add( new Value( true ) );

            static::assertSame( 'VALUES ( true )', $select->stringify() );
        }

        public function testOnePlusOne(): void {
            $select = new Values();
            $select->add(
                (new Expression() )
                    ->add( (new Value( 1 ) ) )
                    ->add( new Operator( '+' ) )
                    ->add( (new Value( 3 ) ) )
            );

            static::assertSame( 'VALUES ( 1 + 3 )', $select->stringify() );
        }

    }
