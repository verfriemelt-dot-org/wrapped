<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Bracket;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class BracketTest
    extends TestCase {

        public function testWrapping(): void {

            $bracket = new Bracket;
            $bracket->add(
                new Value( true )
            );

            static::assertSame( '( true )', $bracket->stringify() );
        }

    }
