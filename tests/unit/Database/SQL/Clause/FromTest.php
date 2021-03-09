<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\From;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Select;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class FromTest
    extends TestCase {

        public function testSimple() {
            $from = new From( new Identifier( 'table' ) );
            $this->assertSame( 'FROM table', $from->stringify() );
        }

        public function testSimpleAlias() {
            $from = new From(
                (new Identifier( 'table' ) )
                    ->addAlias( new Identifier( 'tb' ) )
            );
            $this->assertSame( 'FROM table AS tb', $from->stringify() );
        }

        public function testFromExpression() {
            $from = new From(
                (new Select( ) )->add( new Value( true ) )
            );
            $this->assertSame( 'FROM ( SELECT true )', $from->stringify() );
        }

    }
