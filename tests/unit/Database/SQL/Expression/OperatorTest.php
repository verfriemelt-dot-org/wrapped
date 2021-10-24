<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\OperatorExpression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class OperatorTest
    extends TestCase {

        public function testSimpleOperator() {

            $op = new Operator( '=' );
            $this->assertSame( '=', $op->stringify() );
        }

        public function testOperatorExpression() {
            $op = new OperatorExpression( 'in',
                (new Value( 1 ) ),
                (new Value( 2 ) ),
                (new Value( 3 ) ),
            );

            $this->assertSame( 'IN ( 1, 2, 3 )', $op->stringify() );
        }

    }
