<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\OperatorExpression;
    use \Wrapped\_\Database\SQL\Expression\Value;

    class OperatorTest
    extends TestCase {

        public function testInit() {

            new Operator( '=' );
            new OperatorExpression( 'in', (new Value( 1 ) ) );
        }

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
