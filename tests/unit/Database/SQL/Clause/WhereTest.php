<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Where;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class WhereTest
    extends TestCase {

        public function testSimple() {

            $where = new Where( new Value( true ) );
            $this->assertSame( 'WHERE true', $where->stringify() );
        }

    }
