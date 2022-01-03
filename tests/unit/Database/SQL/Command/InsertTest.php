<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Insert;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;

    class InsertTest
    extends TestCase {

        public function testOne(): void {
            $insert = new Insert( new Identifier( 'table' ) );
            $insert->add( new Identifier( 'column_b' ) );

            static::assertSame( 'INSERT INTO table ( column_b )', $insert->stringify() );
        }

        public function testMore(): void {
            $insert = new Insert( new Identifier( 'table' ) );
            $insert->add( new Identifier( 'column_a' ) );
            $insert->add( new Identifier( 'column_b' ) );

            static::assertSame( 'INSERT INTO table ( column_a, column_b )', $insert->stringify() );
        }

    }
