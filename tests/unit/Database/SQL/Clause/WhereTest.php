<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Expression\Value;

    class WhereTest
    extends TestCase {

        public function testInit() {
            new Where( new Value( true ) );
        }

        public function testSimple() {

            $where = new Where( new Value( true ) );
            $this->assertSame( 'WHERE true', $where->stringify() );
        }

    }
