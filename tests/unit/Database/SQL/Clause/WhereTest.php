<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Expression\Primitive;

    class WhereTest
    extends TestCase {

        public function testInit() {
            new Where( new Primitive( true ) );
        }

        public function testSimple() {

            $where = new Where( new Primitive( true ) );
            $this->assertSame( 'WHERE true', $where->stringify() );
        }

    }
