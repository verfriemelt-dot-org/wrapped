<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Delete;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;

    class DeleteTest
    extends TestCase {

        public function testInit() {
            new Delete( new Identifier( 'table' ) );
        }

        public function testSimple() {

            $delete = new Delete( new Identifier( 'table' ) );
            $this->assertSame( 'DELETE FROM table', $delete->stringify() );
        }

    }
