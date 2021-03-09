<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Limit;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class LimitTest
    extends TestCase {

        public function testSimple() {

            $limit = new Limit(
                (new Value( 1 ) )
            );
            $this->assertSame( 'LIMIT 1', $limit->stringify() );
        }

    }
