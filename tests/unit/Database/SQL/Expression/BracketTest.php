<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Expression\Bracket;
    use \Wrapped\_\Database\SQL\Expression\Value;

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
