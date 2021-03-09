<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Offset;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class OffsetTest
    extends TestCase {

        public function testSimple() {

            $offset = new Offset( (new Value( 1 ) ) );
            $this->assertSame( 'OFFSET 1', $offset->stringify() );
        }

    }
