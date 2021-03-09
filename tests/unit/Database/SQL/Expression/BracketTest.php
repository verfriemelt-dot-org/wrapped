<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Bracket;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class BracketTest
    extends TestCase {

        public function testInit() {
            new Bracket;
        }

        public function testWrapping() {

            $bracket = new Bracket;
            $bracket->add(
                new Value( true )
            );

            $this->assertSame( '( true )', $bracket->stringify() );
        }

    }
