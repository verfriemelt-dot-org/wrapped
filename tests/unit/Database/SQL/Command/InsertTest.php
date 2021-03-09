<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Insert;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;

    class InsertTest
    extends TestCase {

        public function testInit() {
            new Insert( new Identifier( 'table' ) );
        }

        public function testOne() {
            $insert = new Insert( new Identifier( 'table' ) );
            $insert->add( new Identifier( 'column_b' ) );

            $this->assertSame( 'INSERT INTO table ( column_b )', $insert->stringify() );
        }

        public function testMore() {
            $insert = new Insert( new Identifier( 'table' ) );
            $insert->add( new Identifier( 'column_a' ) );
            $insert->add( new Identifier( 'column_b' ) );

            $this->assertSame( 'INSERT INTO table ( column_a, column_b )', $insert->stringify() );
        }

        public function tsetNone() {
            $insert = new Insert( new Identifier( 'table' ) );
        }

    }
