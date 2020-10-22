<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Clause\Limit;
    use \Wrapped\_\Database\SQL\Expression\Value;

    class LimitTest
    extends TestCase {

        public function testSimple() {

            $limit = new Limit(
                (new Value( 1 ) )->useBinding( false )
            );
            $this->assertSame( 'LIMIT 1', $limit->stringify() );
        }

    }
